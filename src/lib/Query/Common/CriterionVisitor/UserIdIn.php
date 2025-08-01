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

final class UserIdIn extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        if (!$criterion instanceof Criterion\UserId) {
            return false;
        }

        return in_array($criterion->operator ?? Operator::IN, [Operator::IN, Operator::EQ], true);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserId $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return sprintf(
            '(%s)',
            implode(
                ' OR ',
                array_map(
                    static fn (string $value): string => 'content_id_id:"' . $value . '"',
                    (array) $criterion->value
                )
            )
        );
    }
}
