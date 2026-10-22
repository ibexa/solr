<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Solr\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Location as SPILocation;
use Ibexa\Contracts\Core\Search\Field;

/**
 * Base class for Location document field mappers.
 *
 * Location document field mapper maps Location to the search fields for Location document.
 */
abstract class LocationFieldMapper
{
    /**
     * Indicates if the mapper accepts given $location for mapping.
     */
    abstract public function accept(SPILocation $location): bool;

    /**
     * Maps given $location to an array of search fields.
     *
     * @return list<Field>
     */
    abstract public function mapFields(SPILocation $location): array;
}
