<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the Visibility criterion.
 */
class Visibility extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Visibility && $criterion->operator === Operator::EQ;
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return 'location_visible_b:' . ($criterion->value[0] === Criterion\Visibility::VISIBLE ? 'true' : 'false');
    }
}

class_alias(Visibility::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\Visibility');
