<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Solr\DependencyInjection;

use Ibexa\Bundle\Solr\DependencyInjection\Configuration;
use Ibexa\Bundle\Solr\DependencyInjection\IbexaSolrExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @phpstan-import-type SolrHttpClientConfigArray from IbexaSolrExtension
 */
class IbexaSolrExtensionExtensionTest extends AbstractExtensionTestCase
{
    private IbexaSolrExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->extension = new IbexaSolrExtension();

        parent::setUp();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
     */
    protected function getContainerExtensions(): array
    {
        return [$this->extension];
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function getMinimalConfiguration(): array
    {
        return [];
    }

    public function testEmpty(): void
    {
        $this->load();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @phpstan-return list<array{string, array<string, mixed>, array<string, mixed>}>
     */
    public function dataProviderForTestEndpoint(): array
    {
        return [
            [
                'endpoint_dsn',
                [
                    'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr',
                    'core' => 'core0',
                ],
                [
                    'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr',
                    'scheme' => 'http',
                    'host' => '127.0.0.1',
                    'port' => 8983,
                    'user' => null,
                    'pass' => null,
                    'path' => '/solr',
                    'core' => 'core0',
                ],
            ],
            [
                'endpoint_standalone',
                [
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ],
                [
                    'dsn' => null,
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ],
            ],
            [
                'endpoint_override',
                [
                    'dsn' => 'https://miles:teg@257.258.259.400:5555/noship',
                    'scheme' => 'http',
                    'host' => 'farm.com',
                    'port' => 1234,
                    'core' => 'core2',
                    'user' => 'darwi',
                    'pass' => 'odrade',
                    'path' => '/dunr',
                ],
                [
                    'dsn' => 'https://miles:teg@257.258.259.400:5555/noship',
                    'scheme' => 'http',
                    'host' => 'farm.com',
                    'port' => 1234,
                    'user' => 'darwi',
                    'pass' => 'odrade',
                    'path' => '/dunr',
                    'core' => 'core2',
                ],
            ],
            [
                'endpoint_defaults',
                [
                    'core' => 'core3',
                ],
                [
                    'dsn' => null,
                    'scheme' => 'http',
                    'host' => '127.0.0.1',
                    'port' => 8983,
                    'user' => null,
                    'pass' => null,
                    'path' => '/solr',
                    'core' => 'core3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestEndpoint
     *
     * @param array<string, mixed> $endpointValues
     * @param array<string, mixed> $expectedArgument
     */
    public function testEndpoint(string $endpointName, array $endpointValues, array $expectedArgument): void
    {
        $this->load(['endpoints' => [$endpointName => $endpointValues]]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            "ibexa.solr.endpoints.{$endpointName}",
            'ibexa.search.solr.endpoint',
            ['alias' => $endpointName]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            "ibexa.solr.endpoints.{$endpointName}",
            0,
            $expectedArgument
        );
    }

    public function testEndpointCoreRequired(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load(
            [
                'endpoints' => [
                    'endpoint0' => [
                        'dsn' => 'https://12.13.14.15:4444/solr',
                    ],
                ],
            ]
        );
    }

    /**
     * @phpstan-return list<array<array-key, mixed>>
     */
    public function dataProviderForTestConnection(): array
    {
        return [
            [
                [
                    'connections' => [],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'entry_endpoints' => [],
                            'mapping' => [],
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'entry_endpoints' => [],
                            'mapping' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestConnection
     */
    public function testConnectionLoad(array $configurationValues): void
    {
        $this->load($configurationValues);
        $alias = $this->extension->getServicePrefix();
        $connectionNames = array_keys($configurationValues['connections']);
        foreach ($connectionNames as $connectionName) {
            $this->assertContainerBuilderHasService("$alias.connection.$connectionName.gateway_id");
        }
        if (empty($connectionNames)) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testConnection(): void
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'entry_endpoints' => [
                        'endpoint1',
                        'endpoint2',
                    ],
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                            'gal-MW' => 'endpoint3',
                        ],
                        'default' => 'endpoint4',
                        'main_translations' => 'endpoint5',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
                'gal-MW' => 'endpoint3',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint4'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint5'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionEndpointDefaults(): void
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                        ],
                        'default' => 'endpoint3',
                        'main_translations' => 'endpoint4',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
                'endpoint3',
                'endpoint4',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint3'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint4'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionEndpointUniqueDefaults(): void
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => [
                        'translations' => [
                            'cro-HR' => 'endpoint1',
                            'eng-GB' => 'endpoint2',
                        ],
                        'default' => 'endpoint2',
                        'main_translations' => 'endpoint2',
                    ],
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
                'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            1,
            [
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint2'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            3,
            'endpoint2'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.gateway_id'
        );
    }

    public function testConnectionMappingDefaults(): void
    {
        $configurationValues = [
            'connections' => [
                'connection1' => [
                    'mapping' => 'endpoint1',
                ],
            ],
        ];

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            0,
            [
                'endpoint1',
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            1,
            []
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            2,
            'endpoint1'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ibexa.solr.connection.connection1.endpoint_resolver_id',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.core_filter_id'
        );
        $this->assertContainerBuilderHasService(
            'ibexa.solr.connection.connection1.gateway_id'
        );
    }

    /**
     * @phpstan-return list<array{array<string, mixed>, array<string, mixed>}>
     */
    public function dataProvideForTestBoostFactorMap(): array
    {
        return [
            [
                [
                    'connections' => [
                        'connection1' => [],
                    ],
                ],
                [],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [],
                        ],
                    ],
                ],
                [],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 1.5,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 1.5,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'field_definition' => [
                                    'title' => 1.5,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        '*' => [
                            'title' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'field_definition' => [
                                    'title' => 2.2,
                                    'article' => [
                                        'title' => 1.5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        '*' => [
                            'title' => 2.2,
                        ],
                        'article' => [
                            'title' => 1.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 5.5,
                                ],
                                'field_definition' => [
                                    'title' => 2.2,
                                    'article' => [
                                        'title' => 1.5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 5.5,
                            'title' => 1.5,
                        ],
                        '*' => [
                            'title' => 2.2,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'content_type' => [
                                    'article' => 5.5,
                                ],
                                'meta_field' => [
                                    'text' => 2.2,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                    ],
                    'meta-fields' => [
                        'article' => [
                            '*' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
            ],
            [
                [
                    'connections' => [
                        'connection1' => [
                            'boost_factors' => [
                                'meta_field' => [
                                    'text' => 2.2,
                                    'article' => [
                                        'name' => 7.8,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'meta-fields' => [
                        '*' => [
                            'text' => 2.2,
                        ],
                        'article' => [
                            'name' => 7.8,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProvideForTestBoostFactorMap
     */
    public function testBoostFactorMap(array $configuration, array $map): void
    {
        $this->load($configuration);

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.connection.connection1.boost_factor_map_id',
            $map
        );
    }

    /**
     * @dataProvider getDataForTestHttpClientConfiguration
     *
     * @phpstan-param SolrHttpClientConfigArray $config
     */
    public function testHttpClientConfiguration(array $config): void
    {
        $this->load(
            [
                'http_client' => $config,
            ]
        );

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.http_client.timeout',
            $config['timeout'],
        );

        $this->assertContainerBuilderHasParameter(
            'ibexa.solr.http_client.max_retries',
            $config['max_retries'],
        );
    }

    /**
     * @return iterable<string, array<SolrHttpClientConfigArray>>
     */
    public function getDataForTestHttpClientConfiguration(): iterable
    {
        yield 'default values' => [
            [
                'timeout' => Configuration::SOLR_HTTP_CLIENT_DEFAULT_TIMEOUT,
                'max_retries' => Configuration::SOLR_HTTP_CLIENT_DEFAULT_MAX_RETRIES,
            ],
        ];

        yield 'custom values' => [
            [
                'timeout' => 16,
                'max_retries' => 2,
            ],
        ];
    }
}
