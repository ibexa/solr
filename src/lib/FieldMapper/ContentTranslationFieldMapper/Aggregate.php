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
    protected $mappers = [];

    /**
     * @param \Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper[] $mappers
     *        An array of mappers, sorted by priority.
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
    public function addMapper(ContentTranslationFieldMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    public function mapFields(Content $content, $languageCode)
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

class_alias(Aggregate::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper\Aggregate');
