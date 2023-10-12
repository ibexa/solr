<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Image\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\FieldType\Image\Type;
use Ibexa\Core\Search\Common\FieldNameResolver;

abstract class AbstractImageVisitor extends CriterionVisitor
{
    private FieldNameResolver $fieldNameResolver;

    private Type $imageFieldType;

    public function __construct(
        FieldNameResolver $fieldNameResolver,
        Type $imageFieldType
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->imageFieldType = $imageFieldType;
    }

    abstract protected function getSearchFieldName(): string;

    /**
     * @return array<string>
     */
    protected function getSearchFieldNames(Criterion $criterion): array
    {
        return array_keys(
            $this->fieldNameResolver->getFieldTypes(
                $criterion,
                $criterion->target,
                $this->imageFieldType->getFieldTypeIdentifier(),
                $this->getSearchFieldName()
            )
        );
    }
}
