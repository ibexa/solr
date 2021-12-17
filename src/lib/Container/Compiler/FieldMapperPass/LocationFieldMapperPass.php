<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Container\Compiler\FieldMapperPass;

use Ibexa\Solr\Container\Compiler\BaseFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Location document.
 */
class LocationFieldMapperPass extends BaseFieldMapperPass
{
    public const AGGREGATE_MAPPER_SERVICE_ID = 'ezpublish.search.solr.field_mapper.location';
    public const AGGREGATE_MAPPER_SERVICE_TAG = self::AGGREGATE_MAPPER_SERVICE_ID;
}

class_alias(LocationFieldMapperPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\FieldMapperPass\LocationFieldMapperPass');
