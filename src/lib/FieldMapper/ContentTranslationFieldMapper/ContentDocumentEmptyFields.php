<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;
use Ibexa\Core\Persistence\FieldTypeRegistry;
use Ibexa\Core\Search\Common\FieldNameGenerator;

/**
 * Indexes information on whether Content field is empty.
 */
class ContentDocumentEmptyFields extends ContentTranslationFieldMapper
{
    public const string IS_EMPTY_NAME = 'is_empty';

    public function __construct(
        private readonly Handler $contentTypeHandler,
        private readonly FieldNameGenerator $fieldNameGenerator,
        private readonly FieldTypeRegistry $fieldTypeRegistry
    ) {
    }

    public function accept(Content $content, string $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, string $languageCode): array
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
                if ($fieldDefinition->isRequired) {
                    continue;
                }
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                /** @var \Ibexa\Core\Persistence\FieldType $fieldType */
                $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldType);
                $fields[] = new Field(
                    $name = $this->fieldNameGenerator->getName(
                        self::IS_EMPTY_NAME,
                        $fieldDefinition->identifier
                    ),
                    $fieldType->isEmptyValue($field->value),
                    new FieldType\BooleanField()
                );
            }
        }

        return $fields;
    }
}
