<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

final class CompositeCriterion extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\CompositeCriterion;
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return $subVisitor->visit($criterion->criteria, $subVisitor);
    }
}

class_alias(CompositeCriterion::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\CompositeCriterion');
