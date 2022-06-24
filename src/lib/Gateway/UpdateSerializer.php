<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway;

use Ibexa\Solr\Gateway\UpdateSerializer\XmlUpdateSerializer;

/**
 * @deprecated use {@see \Ibexa\Solr\Gateway\UpdateSerializer\XmlUpdateSerializer} instead
 *
 * @internal
 */
final class UpdateSerializer extends XmlUpdateSerializer
{
}

class_alias(UpdateSerializer::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\UpdateSerializer');
