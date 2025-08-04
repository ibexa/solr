<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\SetupFactory;

use Doctrine\DBAL\Connection;
use Ibexa\Bundle\Solr\DependencyInjection\IbexaSolrExtension;
use Ibexa\Contracts\Core\Persistence\Content\Handler;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as CoreLegacySetupFactory;
use Ibexa\Core\Base\Container\Compiler\Search\AggregateFieldValueMapperPass;
use Ibexa\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use Ibexa\Core\Base\ServiceContainer;
use Ibexa\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use Ibexa\Solr\Container\Compiler;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;
use Ibexa\Solr\Handler as SolrSearchHandler;
use Ibexa\Solr\Test\SolrTestContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Used to setup the infrastructure for Repository Public API integration tests,
 * based on Repository with Legacy Storage Engine implementation.
 */
class LegacySetupFactory extends CoreLegacySetupFactory
{
    public const array CONFIGURATION_FILES_MAP = SolrTestContainerBuilder::CONFIGURATION_FILES_MAP;

    private readonly SolrTestContainerBuilder $containerBuilder;

    public function __construct()
    {
        parent::__construct();

        $this->containerBuilder = new SolrTestContainerBuilder();
    }

    /**
     * Returns a configured repository for testing.
     */
    #[\Override]
    public function getRepository(bool $initializeFromScratch = true): Repository
    {
        // Load repository first so all initialization steps are done
        $repository = parent::getRepository($initializeFromScratch);

        if ($initializeFromScratch) {
            $this->indexAll();
        }

        return $repository;
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    protected function externalBuildContainer(ContainerBuilder $containerBuilder): void
    {
        parent::externalBuildContainer($containerBuilder);

        $this->loadSolrSettings($containerBuilder);
    }

    protected function loadSolrSettings(ContainerBuilder $containerBuilder): void
    {
        $this->containerBuilder->loadSolrSettings($containerBuilder);

        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\LocationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateCriterionVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSortClauseVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\EndpointRegistryPass());
        $containerBuilder->addCompilerPass(new AggregateFieldValueMapperPass());
        $containerBuilder->addCompilerPass(new FieldRegistryPass());

        $containerBuilder
            ->registerForAutoconfiguration(UpdateSerializerInterface::class)
            ->addTag(IbexaSolrExtension::GATEWAY_UPDATE_SERIALIZER_TAG);

        $containerBuilder->setParameter('ibexa.solr.version', getenv('SOLR_VERSION'));

        $this->configureSymfonyHttpClient($containerBuilder);
    }

    private function getPersistenceContentHandler(
        ServiceContainer $serviceContainer
    ): Handler {
        /** @var \Ibexa\Contracts\Core\Persistence\Content\Handler $contentHandler */
        $contentHandler = $serviceContainer->get(Handler::class);

        return $contentHandler;
    }

    private function getSearchHandler(ServiceContainer $serviceContainer): SolrSearchHandler
    {
        /** @var \Ibexa\Solr\Handler $searchHandler */
        $searchHandler = $serviceContainer->get(SolrSearchHandler::class);

        return $searchHandler;
    }

    private function getDatabaseConnection(ServiceContainer $serviceContainer): Connection
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $serviceContainer->get('ibexa.persistence.connection');

        return $connection;
    }

    /**
     * Indexes all Content objects.
     */
    protected function indexAll(): void
    {
        $serviceContainer = $this->getServiceContainer();
        $contentHandler = $this->getPersistenceContentHandler($serviceContainer);
        $searchHandler = $this->getSearchHandler($serviceContainer);
        $connection = $this->getDatabaseConnection($serviceContainer);

        $query = $connection->createQueryBuilder();
        $query
            ->select('id')
            ->from(ContentGateway::CONTENT_ITEM_TABLE);

        $contentIds = array_map('intval', $query->executeQuery()->fetchFirstColumn());

        $contentItems = $contentHandler->loadContentList($contentIds);

        $searchHandler->purgeIndex();
        $searchHandler->bulkIndexContent($contentItems);
        $searchHandler->commit();
    }

    protected function getTestConfigurationFile(): string
    {
        return $this->containerBuilder->getTestConfigurationFile();
    }

    private function configureSymfonyHttpClient(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setDefinition(
            'http_client',
            (new Definition(HttpClientInterface::class))->setFactory([HttpClient::class, 'create'])
        );
    }
}
