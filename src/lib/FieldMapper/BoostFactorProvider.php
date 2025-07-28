<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Type as ContentType;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;

/**
 * BoostFactorProvider provides boost factors for indexed fields.
 */
class BoostFactorProvider
{
    /**
     * Internal map key used to access Content field boost factors.
     */
    private static string $keyContentFields = 'content-fields';

    /**
     * Internal map key used to access meta field boost factors.
     */
    private static string $keyMetaFields = 'meta-fields';

    /**
     * Internal map wildcard type key.
     */
    private static string $keyAny = '*';

    /**
     * Boost factor to be used if no mapping is found.
     */
    private float $defaultBoostFactor = 1.0;

    /**
     * @param array<string, mixed> $map
     */
    public function __construct(
        /**
         * Internal map of field boost factors.
         *
         * ```php
         * $map = [
         *     'content-fields' => [
         *         '*' => [
         *             'title' => 1.5,
         *             'name' = 2.5,
         *         ],
         *         'article' => [
         *             'title' => 3.0,
         *             '*' => 2.0,
         *         ],
         *         'news' => [
         *             'description' => 3.0,
         *         ],
         *     ],
         *     'meta-fields' => [
         *         '*' => [
         *             'name' = 2.5,
         *             'text' => 1.5,
         *         ],
         *         'article' => [
         *             'name' => 3.0,
         *             '*' => 2.0,
         *         ],
         *         'news' => [
         *             'text' => 2.0,
         *         ],
         *     ],
         * ];
         * ```
         */
        private array $map = []
    ) {
    }

    /**
     * Get boost factor for a Content field by the given $contentType and $fieldDefinition.
     */
    public function getContentFieldBoostFactor(ContentType $contentType, FieldDefinition $fieldDefinition): float
    {
        $typeIdentifier = $contentType->identifier;
        $fieldIdentifier = $fieldDefinition->identifier;

        if (!isset($this->map[self::$keyContentFields][$typeIdentifier])) {
            $typeIdentifier = self::$keyAny;
        }

        if (!isset($this->map[self::$keyContentFields][$typeIdentifier][$fieldIdentifier])) {
            $fieldIdentifier = self::$keyAny;
        }

        return $this->map[self::$keyContentFields][$typeIdentifier][$fieldIdentifier] ?? $this->defaultBoostFactor;
    }

    /**
     * Get boost factor for a Content meta field by the given $fieldName.
     */
    public function getContentMetaFieldBoostFactor(ContentType $contentType, string $fieldName): float
    {
        $typeIdentifier = $contentType->identifier;

        if (!isset($this->map[self::$keyMetaFields][$typeIdentifier])) {
            $typeIdentifier = self::$keyAny;
        }

        if (!isset($this->map[self::$keyMetaFields][$typeIdentifier][$fieldName])) {
            $fieldName = self::$keyAny;
        }

        return $this->map[self::$keyMetaFields][$typeIdentifier][$fieldName] ?? $this->defaultBoostFactor;
    }
}
