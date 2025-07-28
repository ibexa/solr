<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Search\Gateway\EndpointResolver;

use Ibexa\Solr\Gateway\EndpointResolver\NativeEndpointResolver;
use Ibexa\Solr\Gateway\SingleEndpointResolver;
use Ibexa\Tests\Solr\Search\TestCase;
use RuntimeException;

/**
 * Test case for native endpoint resolver.
 */
class NativeEndpointResolverTest extends TestCase
{
    public function testGetEntryEndpoint(): void
    {
        $entryEndpoints = [
            'endpoint2',
            'endpoint0',
            'endpoint1',
        ];

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        self::assertEquals(
            'endpoint2',
            $endpointResolver->getEntryEndpoint()
        );
    }

    public function testGetEntryEndpointThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $entryEndpoints = [];

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        $endpointResolver->getEntryEndpoint();
    }

    public function testGetIndexingTarget(): void
    {
        $endpointMap = [
            'eng-GB' => 'endpoint3',
        ];

        $endpointResolver = $this->getEndpointResolver([], $endpointMap);

        self::assertEquals(
            'endpoint3',
            $endpointResolver->getIndexingTarget('eng-GB')
        );
    }

    public function testGetIndexingTargetReturnsDefaultEndpoint(): void
    {
        $endpointMap = [];
        $defaultEndpoint = 'endpoint4';

        $endpointResolver = $this->getEndpointResolver([], $endpointMap, $defaultEndpoint);

        self::assertEquals(
            'endpoint4',
            $endpointResolver->getIndexingTarget('ger-DE')
        );
    }

    public function getIndexingTargetThrowsRuntimeException(): void
    {
        $endpointResolver = $this->getEndpointResolver();

        $endpointResolver->getIndexingTarget('ger-DE');
    }

    public function testGetMainLanguagesEndpoint(): void
    {
        $mainLanguagesEndpoint = 'endpoint5';

        $endpointResolver = $this->getEndpointResolver([], [], null, $mainLanguagesEndpoint);

        self::assertEquals(
            'endpoint5',
            $endpointResolver->getMainLanguagesEndpoint()
        );
    }

    public function testGetMainLanguagesEndpointReturnsNull(): void
    {
        $endpointResolver = $this->getEndpointResolver();

        self::assertNull($endpointResolver->getMainLanguagesEndpoint());
    }

    /**
     * @return array<int, array{
     *     0: array<string, string>,
     *     1: string|null,
     *     2: string|null,
     *     3: array{
     *          languages?: array<string>,
     *          useAlwaysAvailable?: bool
     *     },
     *     4: array<string>,
     *     5?: bool
     * }>
     */
    public function providerForTestGetSearchTargets(): array
    {
        return [
            // Will return all endpoints (for always available fallback without main languages endpoint)
            0 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            1 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            2 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                ],
                false,
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            3 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            4 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            5 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            6 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            7 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages + main languages endpoint
            8 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'main_languages_endpoint',
                ],
            ],
            // Will return mapped endpoints matched by languages
            9 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            10 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                ],
            ],
            // Will return mapped endpoints matched by languages
            11 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            12 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                ],
            ],
            // Will return mapped endpoints matched by languages
            13 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            14 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            15 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_de_DE',
                ],
            ],
            // Will return mapped endpoints matched by languages
            16 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                        'ger-DE',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            17 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            18 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            19 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (for always available fallback without main languages endpoint)
            20 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            21 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            22 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            23 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages only)
            24 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            25 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            26 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            27 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            28 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                null,
                [],
                [
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            29 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            30 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            31 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            32 => [
                [
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            33 => [
                [],
                'default_endpoint',
                null,
                [],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            34 => [
                [],
                null,
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            35 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            36 => [
                [],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            37 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            38 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
            // Will return all endpoints (search on main languages without main languages endpoint)
            39 => [
                [],
                'default_endpoint',
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                [
                    'default_endpoint',
                ],
                false,
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            40 => [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
            ],
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            41 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                [
                    'main_languages_endpoint',
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetSearchTargets
     *
     * @param string[] $endpointMap
     * @param string[] $expected
     */
    public function testGetSearchTargets(
        array $endpointMap,
        ?string $defaultEndpoint,
        ?string $mainLanguagesEndpoint,
        array $languageSettings,
        array $expected,
        bool $expectedIsMultiple = true
    ): void {
        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $actual = $endpointResolver->getSearchTargets($languageSettings);

        self::assertEquals($expected, $actual);

        if ($endpointResolver instanceof SingleEndpointResolver) {
            self::assertEquals($expectedIsMultiple, $endpointResolver->hasMultipleEndpoints());
        }
    }

    /**
     * @return array<int, array{
     *     0: array<string, string>,
     *     1: string|null,
     *     2: string|null,
     *     3: array{
     *          languages?: array<string>,
     *          useAlwaysAvailable?: bool
     *     },
     *     4: string
     * }>
     */
    public function providerForTestGetSearchTargetsThrowsRuntimeException(): array
    {
        return [
            // Will try to return all endpoints
            0 => [
                [],
                null,
                null,
                [],
                'No endpoints defined',
            ],
            1 => [
                [],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => true,
                ],
                'No endpoints defined',
            ],
            2 => [
                [],
                null,
                null,
                [
                    'languages' => [],
                    'useAlwaysAvailable' => false,
                ],
                'No endpoints defined',
            ],
            3 => [
                [],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                'No endpoints defined',
            ],
            // Will try to map translation
            4 => [
                [],
                null,
                null,
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
            5 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => true,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
            6 => [
                [],
                null,
                'main_languages_endpoint',
                [
                    'languages' => [
                        'eng-GB',
                    ],
                    'useAlwaysAvailable' => false,
                ],
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetSearchTargetsThrowsRuntimeException
     *
     * @param string[] $endpointMap
     */
    public function testGetSearchTargetsThrowsRuntimeException(
        array $endpointMap,
        ?string $defaultEndpoint,
        ?string $mainLanguagesEndpoint,
        array $languageSettings,
        string $message
    ): void {
        $this->expectException(RuntimeException::class);

        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        try {
            $endpointResolver->getSearchTargets($languageSettings);
        } catch (RuntimeException $e) {
            self::assertEquals($message, $e->getMessage());

            throw $e;
        }
    }

    /**
     * @return array{string[], string|null, string|null, string[]}[]
     */
    public function providerForTestGetEndpoints(): array
    {
        return [
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                null,
                null,
                [
                    'endpoint_en_GB',
                ],
            ],
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                'default_endpoint',
                null,
                [
                    'endpoint_en_GB',
                    'default_endpoint',
                ],
            ],
            [
                [
                    'eng-GB' => 'endpoint_en_GB',
                ],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'endpoint_en_GB',
                    'default_endpoint',
                    'main_languages_endpoint',
                ],
            ],
            [
                [],
                'default_endpoint',
                null,
                [
                    'default_endpoint',
                ],
            ],
            [
                [],
                null,
                'main_languages_endpoint',
                [
                    'main_languages_endpoint',
                ],
            ],
            [
                [],
                'default_endpoint',
                'main_languages_endpoint',
                [
                    'default_endpoint',
                    'main_languages_endpoint',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetEndpoints
     *
     * @param string[] $endpointMap
     * @param string[] $expected
     */
    public function testGetEndpoints(
        array $endpointMap,
        ?string $defaultEndpoint,
        ?string $mainLanguagesEndpoint,
        array $expected
    ): void {
        $endpointResolver = $this->getEndpointResolver(
            [],
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $endpoints = $endpointResolver->getEndpoints();

        self::assertEquals($expected, $endpoints);
    }

    public function testGetEndpointsThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $endpointResolver = $this->getEndpointResolver(
            [],
            [],
            null,
            null
        );

        $endpointResolver->getEndpoints();
    }

    /**
     * @dataProvider providerForTestGetEndpoints
     *
     * @param list<string> $entryEndpoints
     * @param array<string, string> $endpointMap
     */
    protected function getEndpointResolver(
        array $entryEndpoints = [],
        array $endpointMap = [],
        ?string $defaultEndpoint = null,
        ?string $mainLanguagesEndpoint = null
    ): NativeEndpointResolver {
        return new NativeEndpointResolver(
            $entryEndpoints,
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );
    }
}
