<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;

final class Height extends AbstractImageRangeVisitor
{
    private const string SEARCH_FIELD_HEIGHT = 'height';

    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\Image\Height
            && (
                $criterion->operator === Operator::BETWEEN
                || $criterion->operator === Operator::GTE
            );
    }

    protected function getSearchFieldName(): string
    {
        return self::SEARCH_FIELD_HEIGHT;
    }
}
