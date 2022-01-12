<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\SortClauseVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

/**
 * Visits the sortClause tree into a Solr query.
 */
class Aggregate extends SortClauseVisitor
{
    /**
     * Array of available visitors.
     *
     * @var \Ibexa\Contracts\Solr\Query\SortClauseVisitor[]
     */
    protected $visitors = [];

    /**
     * Construct from optional visitor array.
     *
     * @param \Ibexa\Contracts\Solr\Query\SortClauseVisitor[] $visitors
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
    public function addVisitor(SortClauseVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($sortClause)) {
                return $visitor->visit($sortClause, $this);
            }
        }

        throw new NotImplementedException('No visitor available for: ' . \get_class($sortClause));
    }
}

class_alias(Aggregate::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor\Aggregate');
