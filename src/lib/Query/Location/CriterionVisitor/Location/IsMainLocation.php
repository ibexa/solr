<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the IsMainLocation criterion.
 */
class IsMainLocation extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Location\IsMainLocation;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        return 'is_main_location_b:' . ($criterion->value[0] === Criterion\Location\IsMainLocation::MAIN ? 'true' : 'false');
    }
}
