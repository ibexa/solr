<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\Image\Type;
use Ibexa\Core\Search\Common\FieldNameResolver;

abstract class AbstractImageVisitor extends CriterionVisitor
{
    public function __construct(
        private readonly FieldNameResolver $fieldNameResolver,
        private readonly Type $imageFieldType
    ) {
    }

    abstract protected function getSearchFieldName(): string;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\AbstractImageRangeCriterion $criterion
     *
     * @return list<string>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    protected function getSearchFieldNames(CriterionInterface $criterion): array
    {
        $searchFieldNames = array_keys(
            $this->fieldNameResolver->getFieldTypes(
                $criterion,
                $criterion->target,
                $this->imageFieldType->getFieldTypeIdentifier(),
                $this->getSearchFieldName()
            )
        );

        if ($searchFieldNames === []) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$criterion->target}'."
            );
        }

        return $searchFieldNames;
    }
}
