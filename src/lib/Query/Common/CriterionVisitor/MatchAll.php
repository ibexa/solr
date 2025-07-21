<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the MatchAll criterion.
 */
class MatchAll extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\MatchAll;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MatchAll $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return '*:*';
    }
}
