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

final class Orientation extends AbstractImageTermsVisitor
{
    private const string SEARCH_FIELD_ORIENTATION = 'orientation';

    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\Image\Orientation
            && (
                $criterion->operator === Operator::EQ
                || $criterion->operator === Operator::IN
            );
    }

    protected function getSearchFieldName(): string
    {
        return self::SEARCH_FIELD_ORIENTATION;
    }
}
