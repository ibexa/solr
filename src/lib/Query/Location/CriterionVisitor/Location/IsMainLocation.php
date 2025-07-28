<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the IsMainLocation criterion.
 */
class IsMainLocation extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\Location\IsMainLocation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return 'is_main_location_b:' . ($criterion->value[0] === Criterion\Location\IsMainLocation::MAIN ? 'true' : 'false');
    }
}
