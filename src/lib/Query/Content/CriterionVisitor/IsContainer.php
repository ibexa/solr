<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

final class IsContainer extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\IsContainer && $criterion->operator === Operator::EQ;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $value = $criterion->value;

        if (!is_array($value) || !is_bool($value[0])) {
            throw new \LogicException(sprintf(
                '%s value should be of type array<bool>, received %s.',
                Criterion\IsContainer::class,
                get_debug_type($value),
            ));
        }

        return 'content_type_is_container_b:' . $this->toString($value[0]);
    }
}
