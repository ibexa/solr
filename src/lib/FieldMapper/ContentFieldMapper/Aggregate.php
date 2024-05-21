<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;

/**
 * Aggregate implementation of Content document field mapper.
 */
class Aggregate extends ContentFieldMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper[]
     */
    protected $mappers = [];

    /**
     * @param \Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper[] $mappers
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
    public function addMapper(ContentFieldMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Content $content)
    {
        return true;
    }

    public function mapFields(Content $content)
    {
        $fields = [];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($content)) {
                $fields = array_merge($fields, $mapper->mapFields($content));
            }
        }

        return $fields;
    }
}
