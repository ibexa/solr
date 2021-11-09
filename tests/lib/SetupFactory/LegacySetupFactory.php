<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Solr\SetupFactory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use eZ\Publish\API\Repository\Tests\SearchServiceTranslationLanguageFallbackTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as CoreLegacySetupFactory;
use eZ\Publish\Core\Base\Container\Compiler\Search\AggregateFieldValueMapperPass;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use Ibexa\Bundle\NamespaceCompatibility\DependencyInjection\Compiler\AliasDecoratorCompatibilityPass;
use Ibexa\Bundle\NamespaceCompatibility\DependencyInjection\Compiler\ServiceCompatibilityPass;
use Ibexa\Solr\Container\Compiler;
use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\SPI\Persistence\Content\Handler;
use Ibexa\Solr\Handler as SolrSearchHandler;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Used to setup the infrastructure for Repository Public API integration tests,
 * based on Repository with Legacy Storage Engine implementation.
 */
class LegacySetupFactory extends CoreLegacySetupFactory
{
    public const CONFIGURATION_FILES_MAP = [
        SearchServiceTranslationLanguageFallbackTest::SETUP_DEDICATED => 'multicore_dedicated.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_SHARED => 'multicore_shared.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_SINGLE => 'single_core.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_CLOUD => 'cloud.yml',
    ];

    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository($initializeFromScratch = true)
    {
        // Load repository first so all initialization steps are done
        $repository = parent::getRepository($initializeFromScratch);

        if ($initializeFromScratch) {
            $this->indexAll();
        }

        return $repository;
    }

    protected function externalBuildContainer(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->addCompilerPass(
            new ServiceCompatibilityPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            128
        );
        $containerBuilder->addCompilerPass(
            new AliasDecoratorCompatibilityPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            127
        );

        parent::externalBuildContainer($containerBuilder);

        $this->loadSolrSettings($containerBuilder);
    }

    protected function loadSolrSettings(ContainerBuilder $containerBuilder): void
    {
        $settingsPath = realpath(__DIR__ . '/../../../src/lib/Resources/config/container/');
        $testSettingsPath = realpath(__DIR__ . '/../Resources/config/');

        $solrLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
        $solrLoader->load('solr.yml');

        $solrTestLoader = new YamlFileLoader($containerBuilder, new FileLocator($testSettingsPath));
        $solrTestLoader->load($this->getTestConfigurationFile());

        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\BlockTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\ContentTranslationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\FieldMapperPass\LocationFieldMapperPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateCriterionVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateFacetBuilderVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\AggregateSortClauseVisitorPass());
        $containerBuilder->addCompilerPass(new Compiler\EndpointRegistryPass());
        $containerBuilder->addCompilerPass(new AggregateFieldValueMapperPass());
        $containerBuilder->addCompilerPass(new FieldRegistryPass());
    }

    private function getPersistenceContentHandler(
        ServiceContainer $serviceContainer
    ): Handler {
        /** @var \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler */
        $contentHandler = $serviceContainer->get('ezpublish.spi.persistence.content_handler');

        return $contentHandler;
    }

    private function getSearchHandler(ServiceContainer $serviceContainer): SolrSearchHandler
    {
        /** @var \EzSystems\EzPlatformSolrSearchEngine\Handler $searchHandler */
        $searchHandler = $serviceContainer->get('ezpublish.spi.search.solr');

        return $searchHandler;
    }

    private function getDatabaseConnection(ServiceContainer $serviceContainer): Connection
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $serviceContainer->get('ezpublish.persistence.connection');

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

        $contentIds = array_map('intval', $query->execute()->fetchAll(FetchMode::COLUMN));

        $contentItems = $contentHandler->loadContentList($contentIds);

        $searchHandler->purgeIndex();
        $searchHandler->bulkIndexContent($contentItems);
        $searchHandler->commit();
    }

    protected function getTestConfigurationFile(): string
    {
        $isSolrCloud = getenv('SOLR_CLOUD') === 'yes';
        $coresSetup = $isSolrCloud
            ? SearchServiceTranslationLanguageFallbackTest::SETUP_CLOUD
            : getenv('CORES_SETUP');

        if (!isset(self::CONFIGURATION_FILES_MAP[$coresSetup])) {
            throw new RuntimeException("Backend cores setup '{$coresSetup}' is not handled");
        }

        return self::CONFIGURATION_FILES_MAP[$coresSetup];
    }
}

class_alias(LegacySetupFactory::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory');
