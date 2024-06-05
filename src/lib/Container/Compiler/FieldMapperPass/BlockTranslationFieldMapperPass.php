<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Container\Compiler\FieldMapperPass;

use Ibexa\Solr\Container\Compiler\BaseFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the block documents
 * in a specific translation.
 */
class BlockTranslationFieldMapperPass extends BaseFieldMapperPass
{
    public const AGGREGATE_MAPPER_SERVICE_ID = 'ibexa.solr.field_mapper.block_translation';
    public const AGGREGATE_MAPPER_SERVICE_TAG = 'ibexa.search.solr.field.mapper.block.translation';
}
