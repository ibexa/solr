<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Container\Compiler\FieldMapperPass;

use Ibexa\Solr\Container\Compiler\BaseFieldMapperPass;

/**
 * Compiler pass for aggregate document field mapper for the Content document
 * in a specific translation.
 */
class ContentTranslationFieldMapperPass extends BaseFieldMapperPass
{
    public const string AGGREGATE_MAPPER_SERVICE_ID = 'ibexa.solr.field_mapper.content_translation';
    public const string AGGREGATE_MAPPER_SERVICE_TAG = 'ibexa.search.solr.field.mapper.content.translation';
}
