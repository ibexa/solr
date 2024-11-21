<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * @internal
 */
abstract class BaseIsContainer extends CriterionVisitor
{
    abstract protected function getTargetField(): string;

    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\IsContainer && $criterion->operator === Operator::EQ;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsContainer $criterion
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $value = $criterion->value;

        if (!is_array($value) || !is_bool($value[0])) {
            throw new \LogicException(sprintf(
                '%s value should be of type array<bool>, received %s.',
                Criterion\IsContainer::class,
                get_debug_type($value),
            ));
        }

        return $this->getTargetField() . ':' . $this->toString($value[0]);
    }
}
