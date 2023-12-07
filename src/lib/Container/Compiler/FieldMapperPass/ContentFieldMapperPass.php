<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Container\Compiler\FieldMapperPass;

use Ibexa\Solr\Container\Compiler\BaseFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Content document.
 */
class ContentFieldMapperPass extends BaseFieldMapperPass
{
    public const AGGREGATE_MAPPER_SERVICE_ID = 'ibexa.solr.field_mapper.content';
    public const AGGREGATE_MAPPER_SERVICE_TAG = 'ibexa.search.solr.field.mapper.content';
}

class_alias(ContentFieldMapperPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\FieldMapperPass\ContentFieldMapperPass');
