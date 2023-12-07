<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Solr\Query\Common\CriterionVisitor\Field as FieldBase;

/**
 * Kept for BC reasons.
 *
 * @deprecated since 1.2, to be removed in 2.0. Use extended class instead.
 * @see \Ibexa\Solr\Query\Common\CriterionVisitor\Field
 */
abstract class Field extends FieldBase
{
}

class_alias(Field::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\Field');
