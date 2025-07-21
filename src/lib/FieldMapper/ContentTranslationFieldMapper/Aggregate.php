<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;

/**
 * Aggregate implementation of Content translation document field mapper.
 */
class Aggregate extends ContentTranslationFieldMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper[]
     */
    protected array $mappers = [];

    /**
     * @param \Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper[] $mappers An array of mappers, sorted by priority.
     */
    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * Adds given $mapper to the internal array as the next one in priority.
     */
    public function addMapper(ContentTranslationFieldMapper $mapper): void
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content, string $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, string $languageCode): array
    {
        $fields = [];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($content, $languageCode)) {
                $fields = array_merge($fields, $mapper->mapFields($content, $languageCode));
            }
        }

        return $fields;
    }
}
