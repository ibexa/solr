<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Type as ContentType;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Core\Search\FieldType\TextField;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;
use Ibexa\Core\Search\Common\FieldNameGenerator;
use Ibexa\Core\Search\Common\FieldRegistry;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;

/**
 * Maps Content fields to block documents (Content and Location).
 */
class BlockDocumentsContentFields extends ContentTranslationFieldMapper
{
    protected Handler $contentTypeHandler;

    protected FieldRegistry $fieldRegistry;

    protected FieldNameGenerator $fieldNameGenerator;

    protected BoostFactorProvider $boostFactorProvider;

    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        BoostFactorProvider $boostFactorProvider
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->boostFactorProvider = $boostFactorProvider;
    }

    public function accept(Content $content, $languageCode): bool
    {
        return true;
    }

    /**
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    public function mapFields(Content $content, $languageCode): array
    {
        $fields = [];
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId || !$fieldDefinition->isSearchable) {
                    continue;
                }

                $fieldType = $this->fieldRegistry->getType($field->type);
                $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    if ($indexField->type instanceof FieldType\FullTextField) {
                        continue;
                    }

                    $fields[] = new Field(
                        $name = $this->fieldNameGenerator->getName(
                            $indexField->name,
                            $fieldDefinition->identifier,
                            $contentType->identifier
                        ),
                        $indexField->value,
                        $this->getIndexFieldType($contentType, $fieldDefinition, $indexField->type)
                    );
                }
            }
        }

        return $fields;
    }

    /**
     * Return index field type for the given arguments.
     *
     * @return \Ibexa\Contracts\Core\Search\FieldType
     */
    private function getIndexFieldType(
        ContentType $contentType,
        FieldDefinition $fieldDefinition,
        FieldType $fieldType
    ): FieldType|TextField {
        if (!$fieldType instanceof TextField) {
            return $fieldType;
        }

        $fieldType = clone $fieldType;
        $fieldType->boost = $this->boostFactorProvider->getContentFieldBoostFactor(
            $contentType,
            $fieldDefinition
        );

        return $fieldType;
    }
}
