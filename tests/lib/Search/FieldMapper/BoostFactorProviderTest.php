<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Search\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Type;
use Ibexa\Contracts\Core\Persistence\Content\Type as SPIContentType;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;
use Ibexa\Tests\Solr\Search\TestCase;

/**
 * Test case for the boost factor provider.
 */
class BoostFactorProviderTest extends TestCase
{
    /**
     * @return array{array<string, mixed>, string, string, float}[]
     */
    public function providerForTestGetContentFieldBoostFactor(): array
    {
        return [
            [
                [
                    'content-fields' => [
                        'article' => [
                            'title' => 5.5,
                        ],
                    ],
                ],
                'article',
                'title',
                5.5,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            'title' => 5.5,
                        ],
                    ],
                ],
                'blog_post',
                'title',
                1.0,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'news',
                'name',
                2.2,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'title' => 5.5,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'news',
                'title',
                1.0,
            ],
            [
                [
                    'content-fields' => [
                        'article' => [
                            '*' => 3.3,
                        ],
                        '*' => [
                            'name' => 2.2,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetContentFieldBoostFactor
     *
     * @param array{meta-fields: array<string, array<string, float>>} $map
     */
    public function testGetContentFieldBoostFactor(
        array $map,
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        float $expectedBoostFactor
    ): void {
        $provider = $this->getFieldBoostProvider($map);

        $boostFactor = $provider->getContentFieldBoostFactor(
            $this->getContentTypeStub($contentTypeIdentifier),
            $this->getFieldDefinitionStub($fieldDefinitionIdentifier)
        );

        self::assertEquals($expectedBoostFactor, $boostFactor);
    }

    /**
     * @return array{array<string, mixed>, string, string, float}[]
     */
    public function providerForTestGetContentMetaFieldBoostFactor(): array
    {
        return [
            [
                [
                    'meta-fields' => [
                        'article' => [
                            'name' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                5.5,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            'name' => 5.5,
                        ],
                    ],
                ],
                'article',
                'text',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'text' => 5.5,
                        ],
                    ],
                ],
                'article',
                'name',
                3.3,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'text' => 5.5,
                        ],
                    ],
                ],
                'blog_post',
                'name',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'name' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'news',
                'text',
                2.2,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                            'name' => 5.5,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'news',
                'name',
                1.0,
            ],
            [
                [
                    'meta-fields' => [
                        'article' => [
                            '*' => 3.3,
                        ],
                        '*' => [
                            'text' => 2.2,
                        ],
                    ],
                ],
                'article',
                'text',
                3.3,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestGetContentMetaFieldBoostFactor
     *
     * @param array{meta-fields: array<string, array<string, float>>} $map
     */
    public function testGetContentMetaFieldBoostFactor(
        array $map,
        string $contentTypeIdentifier,
        string $fieldName,
        float $expectedBoostFactor
    ): void {
        $provider = $this->getFieldBoostProvider($map);

        $boostFactor = $provider->getContentMetaFieldBoostFactor(
            $this->getContentTypeStub($contentTypeIdentifier),
            $fieldName
        );

        self::assertEquals($expectedBoostFactor, $boostFactor);
    }

    /**
     * @param array{meta-fields: array<string, array<string, float>>} $map
     */
    protected function getFieldBoostProvider(array $map): BoostFactorProvider
    {
        return new BoostFactorProvider($map);
    }

    protected function getContentTypeStub(string $identifier): Type
    {
        return new SPIContentType(
            [
                'identifier' => $identifier,
            ]
        );
    }

    protected function getFieldDefinitionStub(string $identifier): FieldDefinition
    {
        return new SPIFieldDefinition(
            [
                'identifier' => $identifier,
            ]
        );
    }
}
