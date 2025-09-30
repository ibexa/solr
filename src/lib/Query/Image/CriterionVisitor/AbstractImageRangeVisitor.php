<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

abstract class AbstractImageRangeVisitor extends AbstractImageVisitor
{
    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var array{0: int, 1?: int|null} $criterionValue */
        $criterionValue = $criterion->value;
        $queries = [];

        foreach ($this->getSearchFieldNames($criterion) as $fieldName) {
            $queries[] = $fieldName . ':' . $this->getRange(
                $criterion->operator,
                $criterionValue[0],
                $criterionValue[1] ?? null
            );
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
