<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the criterion tree into a Solr query.
 */
class Aggregate extends CriterionVisitor
{
    /**
     * Array of available visitors.
     *
     * @var iterable<\Ibexa\Contracts\Solr\Query\CriterionVisitor>
     */
    protected iterable $visitors;

    /**
     * Construct from optional visitor array.
     *
     * @param iterable<\Ibexa\Contracts\Solr\Query\CriterionVisitor> $visitors
     */
    public function __construct(iterable $visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return true;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($criterion)) {
                return $visitor->visit($criterion, $this);
            }
        }

        throw new NotImplementedException('No visitor available for: ' . $criterion::class);
    }
}
