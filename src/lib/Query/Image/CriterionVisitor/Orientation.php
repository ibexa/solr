<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;

final class Orientation extends AbstractImageTermsVisitor
{
    private const SEARCH_FIELD_ORIENTATION = 'orientation';

    public function canVisit(Criterion $criterion): bool
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
