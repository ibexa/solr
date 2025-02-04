<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr\DependencyInjection;

use Ibexa\Bundle\Solr\ApiLoader\BoostFactorProviderFactory;
use Ibexa\Bundle\Solr\ApiLoader\SolrEngineFactory;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;
use Ibexa\Solr\Gateway\DistributionStrategy\CloudDistributionStrategy;
use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;
use Ibexa\Solr\Handler;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @phpstan-type SolrHttpClientConfigArray = array{timeout: int, max_retries: int}
 */
class IbexaSolrExtension extends ConfigurableExtension
{
    /**
     * Main Solr search handler service ID.
     *
     * @phpstan-var class-string
     */
    public const string ENGINE_ID = Handler::class;

    /**
     * Configured core gateway service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     */
    public const string GATEWAY_ID = 'ibexa.solr.gateway.native';

    /**
     * Configured core filter service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     */
    public const string CORE_FILTER_ID = 'ibexa.solr.core_filter.native';

    /**
     * Configured core endpoint resolver service ID.
     *
     * Not using service alias since alias can't be passed for decoration.
     */
    public const string ENDPOINT_RESOLVER_ID = 'ibexa.solr.gateway.endpoint_resolver.native';

    /** @phpstan-var class-string */
    public const string ENDPOINT_CLASS = Endpoint::class;

    public const string ENDPOINT_TAG = 'ibexa.search.solr.endpoint';

    /** @phpstan-var class-string */
    public const string BOOST_FACTOR_PROVIDER_ID = BoostFactorProvider::class;

    public const string STANDALONE_DISTRIBUTION_STRATEGY_ID = 'ibexa.solr.gateway.distribution_strategy.abstract_standalone';

    /** @phpstan-var class-string */
    public const string CLOUD_DISTRIBUTION_STRATEGY_ID = CloudDistributionStrategy::class;

    public const string GATEWAY_UPDATE_SERIALIZER_TAG = 'ibexa.solr.gateway.serializer.update';

    public function getAlias(): string
    {
        return 'ibexa_solr';
    }

    public function getServicePrefix(): string
    {
        return 'ibexa.solr';
    }

    /**
     * @param array<string, mixed> $mergedConfig
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @throws \Exception
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        // Loading configuration from lib/Resources/config/container
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../lib/Resources/config/container')
        );
        $loader->load('solr.yml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $this->processConnectionConfiguration($container, $mergedConfig);

        $container
            ->registerForAutoconfiguration(UpdateSerializerInterface::class)
            ->addTag(self::GATEWAY_UPDATE_SERIALIZER_TAG);
    }

    /**
     * Processes connection configuration by flattening connection parameters
     * and setting them to the container as parameters.
     *
     * @param array<string, mixed> $config
     */
    protected function processConnectionConfiguration(ContainerBuilder $container, array $config): void
    {
        $alias = $this->getServicePrefix();

        if (isset($config['default_connection'])) {
            $container->setParameter(
                "{$alias}.default_connection",
                $config['default_connection']
            );
        } elseif (!empty($config['connections'])) {
            reset($config['connections']);
            $container->setParameter(
                "{$alias}.default_connection",
                key($config['connections'])
            );
        }

        foreach ($config['connections'] as $name => $params) {
            $this->configureSearchServices($container, $name, $params);
            $this->configureBoostMap($container, $name, $params);
            $this->configureIndexingDepth($container, $name, $params);

            $container->setParameter("$alias.connection.$name", $params);
        }

        foreach ($config['endpoints'] as $name => $params) {
            $this->defineEndpoint($container, $name, $params);
        }

        // Search engine itself, for given connection name
        $searchEngineDef = $container->findDefinition(self::ENGINE_ID);
        $searchEngineDef->setFactory([new Reference(SolrEngineFactory::class), 'buildEngine']);

        // Factory for BoostFactorProvider uses mapping configured for the connection in use
        $boostFactorProviderDef = $container->findDefinition(self::BOOST_FACTOR_PROVIDER_ID);
        $boostFactorProviderDef->setFactory([new Reference(BoostFactorProviderFactory::class), 'buildService']);

        if (isset($config['http_client'])) {
            $this->configureHttpClient($container, $config['http_client']);
        }
    }

    /**
     * Creates needed search services for given connection name and parameters.
     *
     * @param array<string, mixed> $connectionParams
     */
    private function configureSearchServices(
        ContainerBuilder $container,
        string $connectionName,
        array $connectionParams
    ): void {
        $alias = $this->getServicePrefix();

        // Endpoint resolver
        $endpointResolverDefinition = new ChildDefinition(self::ENDPOINT_RESOLVER_ID);
        $endpointResolverDefinition->replaceArgument(0, $connectionParams['entry_endpoints']);
        $endpointResolverDefinition->replaceArgument(1, $connectionParams['mapping']['translations']);
        $endpointResolverDefinition->replaceArgument(2, $connectionParams['mapping']['default']);
        $endpointResolverDefinition->replaceArgument(3, $connectionParams['mapping']['main_translations']);
        $endpointResolverId = "$alias.connection.$connectionName.endpoint_resolver_id";
        $container->setDefinition($endpointResolverId, $endpointResolverDefinition);

        // Core filter
        $coreFilterDefinition = new ChildDefinition(self::CORE_FILTER_ID);
        $coreFilterDefinition->replaceArgument(0, new Reference($endpointResolverId));
        $coreFilterDefinition->addTag('ibexa.search.solr.core.filter', ['connection' => $connectionName]);
        $coreFilterId = "$alias.connection.$connectionName.core_filter_id";
        $container->setDefinition($coreFilterId, $coreFilterDefinition);

        // Distribution Strategy
        $distributionStrategyId = "$alias.connection.$connectionName.distribution_strategy";

        switch ($connectionParams['distribution_strategy']) {
            case 'standalone':
                $distributionStrategyDefinition = new ChildDefinition(self::STANDALONE_DISTRIBUTION_STRATEGY_ID);
                $distributionStrategyDefinition->setArgument(1, new Reference($endpointResolverId));
                break;
            case 'cloud':
                $distributionStrategyDefinition = new ChildDefinition(self::CLOUD_DISTRIBUTION_STRATEGY_ID);
                $distributionStrategyDefinition->setArgument(1, new Reference($endpointResolverId));
                break;
            default:
                throw new \RuntimeException('Unknown distribution strategy');
        }

        $container->setDefinition($distributionStrategyId, $distributionStrategyDefinition);

        // Gateway
        $gatewayDefinition = new ChildDefinition(self::GATEWAY_ID);
        $gatewayDefinition->replaceArgument('$endpointResolver', new Reference($endpointResolverId));
        $gatewayDefinition->replaceArgument('$distributionStrategy', new Reference($distributionStrategyId));
        $gatewayDefinition->addTag('ibexa.search.solr.gateway', ['connection' => $connectionName]);

        $gatewayId = "$alias.connection.$connectionName.gateway_id";
        $container->setDefinition($gatewayId, $gatewayDefinition);
    }

    /**
     * Creates boost factor map parameter for a given $connectionName.
     *
     * @param array<string, mixed> $connectionParams
     */
    private function configureBoostMap(
        ContainerBuilder $container,
        string $connectionName,
        array $connectionParams
    ): void {
        $alias = $this->getServicePrefix();
        $boostFactorMap = $this->buildBoostFactorMap($connectionParams['boost_factors']);
        $boostFactorMapId = "{$alias}.connection.{$connectionName}.boost_factor_map_id";

        $container->setParameter($boostFactorMapId, $boostFactorMap);
    }

    /**
     * Creates indexing depth map parameter for a given $connectionName.
     *
     * @param array<string, mixed> $connectionParams
     */
    private function configureIndexingDepth(ContainerBuilder $container, string $connectionName, array $connectionParams): void
    {
        $alias = $this->getServicePrefix();

        $defaultIndexingDepthId = "{$alias}.connection.{$connectionName}.indexing_depth.default";
        $contentTypeIndexingDepthMapId = "{$alias}.connection.{$connectionName}.indexing_depth.map";

        $container->setParameter($defaultIndexingDepthId, $connectionParams['indexing_depth']['default']);
        $container->setParameter($contentTypeIndexingDepthMapId, $connectionParams['indexing_depth']['content_type']);
    }

    /**
     * Creates Endpoint definition in the service container.
     *
     * @param array<mixed> $params
     */
    protected function defineEndpoint(ContainerBuilder $container, string $alias, array $params): void
    {
        $definition = new Definition(self::ENDPOINT_CLASS, [$params]);
        $definition->addTag(self::ENDPOINT_TAG, ['alias' => $alias]);

        $container->setDefinition(
            sprintf($this->getServicePrefix() . '.endpoints.%s', $alias),
            $definition
        );
    }

    /**
     * @param array<array<mixed>> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new Configuration($this->getAlias());
    }

    /**
     * Builds boost factor map from the given $config.
     *
     * @see \Ibexa\Solr\FieldMapper\BoostFactorProvider::$map
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function buildBoostFactorMap(array $config): array
    {
        $boostFactorMap = [];

        foreach ($config['content_type'] as $typeIdentifier => $factor) {
            $boostFactorMap['content-fields'][$typeIdentifier]['*'] = $factor;
            $boostFactorMap['meta-fields'][$typeIdentifier]['*'] = $factor;
        }

        foreach ($config['field_definition'] as $typeIdentifier => $mapping) {
            foreach ($mapping as $fieldIdentifier => $factor) {
                $boostFactorMap['content-fields'][$typeIdentifier][$fieldIdentifier] = $factor;
            }
        }

        foreach ($config['meta_field'] as $typeIdentifier => $mapping) {
            foreach ($mapping as $fieldIdentifier => $factor) {
                $boostFactorMap['meta-fields'][$typeIdentifier][$fieldIdentifier] = $factor;
            }
        }

        return $boostFactorMap;
    }

    /**
     * @phpstan-param SolrHttpClientConfigArray $httpClientConfig
     */
    private function configureHttpClient(ContainerBuilder $container, array $httpClientConfig): void
    {
        $container->setParameter('ibexa.solr.http_client.timeout', $httpClientConfig['timeout']);
        $container->setParameter(
            'ibexa.solr.http_client.max_retries',
            $httpClientConfig['max_retries']
        );
    }
}
