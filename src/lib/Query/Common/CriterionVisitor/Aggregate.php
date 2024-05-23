<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the criterion tree into a Solr query.
 */
class Aggregate extends CriterionVisitor
{
    /**
     * Array of available visitors.
     *
     * @var \Ibexa\Contracts\Solr\Query\CriterionVisitor[]
     */
    protected $visitors = [];

    /**
     * Construct from optional visitor array.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     */
    public function addVisitor(CriterionVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($criterion)) {
                return $visitor->visit($criterion, $this);
            }
        }

        throw new NotImplementedException('No visitor available for: ' . \get_class($criterion) . ' with operator ' . $criterion->operator);
    }
}

class_alias(Aggregate::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Aggregate');
