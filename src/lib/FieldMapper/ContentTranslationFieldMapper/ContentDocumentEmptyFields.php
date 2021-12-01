<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
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
    public const IS_EMPTY_NAME = 'is_empty';

    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * @var \Ibexa\Core\Search\Common\FieldNameGenerator
     */
    private $fieldNameGenerator;

    /**
     * @var \Ibexa\Core\Persistence\FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $fieldNameGenerator,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * @param string $languageCode
     *
     * @return bool
     */
    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    /**
     * @param string $languageCode
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    public function mapFields(Content $content, $languageCode)
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

class_alias(ContentDocumentEmptyFields::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper\ContentDocumentEmptyFields');
