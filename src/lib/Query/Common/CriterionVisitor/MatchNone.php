<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the MatchNone criterion.
 */
class MatchNone extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\MatchNone;
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return '(NOT *:*)';
    }
}

class_alias(MatchNone::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\MatchNone');
