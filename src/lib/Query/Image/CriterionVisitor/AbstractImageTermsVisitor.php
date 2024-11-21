<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

abstract class AbstractImageTermsVisitor extends AbstractImageVisitor
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\AbstractImageRangeCriterion $criterion
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $queries = [];
        /** @var array<string>|string $criterionValue */
        $criterionValue = $criterion->value;

        foreach ($this->getSearchFieldNames($criterion) as $fieldName) {
            if (is_array($criterionValue)) {
                foreach ($criterionValue as $value) {
                    $queries[] = $fieldName . ':' . $value;
                }
            }

            if (is_string($criterionValue)) {
                $queries[] = $fieldName . ':' . $criterionValue;
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
