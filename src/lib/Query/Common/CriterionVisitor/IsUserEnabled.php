<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

final class IsUserEnabled extends CriterionVisitor
{
    private const SEARCH_FIELD = 'user_is_enabled_b';

    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\IsUserEnabled;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsUserEnabled $criterion
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $value = $criterion->value;
        if (!is_array($value) || !is_bool($value[0])) {
            throw new \LogicException(
                sprintf(
                    '%s value should be of type array<bool>, received %s.',
                    Criterion\IsUserEnabled::class,
                    get_debug_type($value),
                )
            );
        }

        return self::SEARCH_FIELD . ':' . $this->toString($value[0]);
    }
}
