<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * @internal
 */
final class ContentName extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\ContentName
            && $criterion->operator === Criterion\Operator::LIKE;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentName $criterion
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        /** @var string $value */
        $value = $criterion->value;
        $searchField = 'meta_content__name_s';

        return "{!edismax v='{$this->escapeQuote($value)}' qf='{$searchField}' uf=-*}";
    }
}
