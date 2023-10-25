<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Solr;

use Ibexa\Tests\Integration\Core\Repository\SearchServiceTranslationLanguageFallbackTest;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @internal
 */
final class SolrTestContainerBuilder
{
    public const CONFIGURATION_FILES_MAP = [
        SearchServiceTranslationLanguageFallbackTest::SETUP_DEDICATED => 'multicore_dedicated.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_SHARED => 'multicore_shared.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_SINGLE => 'single_core.yml',
        SearchServiceTranslationLanguageFallbackTest::SETUP_CLOUD => 'cloud.yml',
    ];

    public function loadSolrSettings(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('test.ibexa.solr.host', getenv('SOLR_HOST') ?: 'localhost');

        $settingsPath = dirname(__DIR__, 2) . '/src/lib/Resources/config/container/';
        $testSettingsPath = dirname(__DIR__) . '/lib/Resources/config/';

        $solrLoader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));
        $solrLoader->load('solr.yml');

        $testConfigurationFile = $this->getTestConfigurationFile();
        $solrTestLoader = new YamlFileLoader($containerBuilder, new FileLocator($testSettingsPath));
        $solrTestLoader->load($testConfigurationFile);

        $containerBuilder->addResource(new FileResource($testSettingsPath . $testConfigurationFile));
    }

    public function getTestConfigurationFile(): string
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
