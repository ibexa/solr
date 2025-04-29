<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor\Field;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Core\Search\FieldType\BooleanField;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Search\Common\FieldNameGenerator;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Core\Search\Common\FieldValueMapper;
use Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper\ContentDocumentEmptyFields;
use Ibexa\Solr\Query\Common\CriterionVisitor\Field;

/**
 * Visits the IsFieldEmpty criterion.
 */
final class FieldEmpty extends Field
{
    private FieldNameGenerator $fieldNameGenerator;

    public function __construct(
        FieldNameResolver $fieldNameResolver,
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $fieldNameGenerator
    ) {
        parent::__construct($fieldNameResolver, $fieldValueMapper);

        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\IsFieldEmpty;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsFieldEmpty $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $searchFields = $this->getSearchFields($criterion);

        if (empty($searchFields)) {
            throw new InvalidArgumentException('$criterion->target', "No searchable fields found for the given criterion target '{$criterion->target}'.");
        }

        $criterion->value = (array)$criterion->value;
        $queries = [];

        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                $name = $this->fieldNameGenerator->getTypedName(
                    $this->fieldNameGenerator->getName(
                        ContentDocumentEmptyFields::IS_EMPTY_NAME,
                        $criterion->target
                    ),
                    new BooleanField()
                );
                $queries[] = $name . ':' . (int) $value;
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
