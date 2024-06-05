<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\LocationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Solr\FieldMapper\LocationFieldMapper;

/**
 * Aggregate implementation of Location document field mapper.
 */
class Aggregate extends LocationFieldMapper
{
    /**
     * An array of aggregated field mappers, sorted by priority.
     *
     * @var \Ibexa\Contracts\Solr\FieldMapper\LocationFieldMapper[]
     */
    protected $mappers = [];

    /**
     * @param \Ibexa\Contracts\Solr\FieldMapper\LocationFieldMapper[] $mappers
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
    public function addMapper(LocationFieldMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    public function accept(Location $location)
    {
        return true;
    }

    public function mapFields(Location $location)
    {
        $fields = [];

        foreach ($this->mappers as $mapper) {
            if ($mapper->accept($location)) {
                $fields = array_merge($fields, $mapper->mapFields($location));
            }
        }

        return $fields;
    }
}
