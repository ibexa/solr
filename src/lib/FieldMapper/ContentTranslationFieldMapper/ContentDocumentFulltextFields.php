<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Handler;
use Ibexa\Contracts\Core\Persistence\Content\Type as ContentType;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Core\Search\FieldType\TextField;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;
use Ibexa\Core\Search\Common\FieldNameGenerator;
use Ibexa\Core\Search\Common\FieldRegistry;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentFulltextFields extends ContentTranslationFieldMapper
{
    /**
     * Field name, untyped.
     */
    private static string $fieldName = 'meta_content__text';

    /**
     * Field of related content name, untyped.
     */
    private static string $relatedContentFieldName = 'meta_related_content_%d__text';

    public function __construct(
        protected readonly ContentTypeHandler $contentTypeHandler,
        protected readonly Handler $contentHandler,
        protected readonly FieldRegistry $fieldRegistry,
        protected readonly FieldNameGenerator $fieldNameGenerator,
        protected readonly BoostFactorProvider $boostFactorProvider,
        protected readonly IndexingDepthProvider $indexingDepthProvider
    ) {
    }

    public function accept(Content $content, string $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, string $languageCode): array
    {
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        $maxDepth = $this->indexingDepthProvider->getMaxDepthForContent(
            $contentType
        );

        return $this->doMapFields($content, $contentType, $languageCode, $maxDepth);
    }

    /**
     * @return list<\Ibexa\Contracts\Core\Search\Field>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function doMapFields(
        Content $content,
        ContentType $contentType,
        string $languageCode,
        int $maxDepth,
        int $depth = 0
    ): array {
        $fields = [];

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

                    if (!$indexField->type instanceof FieldType\FullTextField) {
                        continue;
                    }

                    $fields[] = new Field(
                        $this->getIndexFieldName($depth),
                        $indexField->value,
                        $this->getIndexFieldType($contentType)
                    );
                }
            }
        }

        if ($depth < $maxDepth) {
            $relatedFields = $this->doMapRelatedFields($content, $languageCode, $maxDepth, $depth + 1);
            foreach ($relatedFields as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Maps given $content relations to an array of search fields.
     *
     * @param string $languageCode
     * @param int $maxDepth
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function doMapRelatedFields(Content $sourceContent, $languageCode, $maxDepth, int $depth): array
    {
        $sourceContentId = $sourceContent->versionInfo->contentInfo->id;
        $relations = $this->contentHandler->loadRelationList(
            $sourceContentId,
            $this->contentHandler->countRelations($sourceContentId)
        );

        $relatedContents = $this->contentHandler->loadContentList(
            array_map(static fn (Content\Relation $relation): int => $relation->destinationContentId, $relations)
        );

        $contentTypes = $this->contentTypeHandler->loadContentTypeList(
            array_map(static fn (Content $content): int => $content->versionInfo->contentInfo->contentTypeId, $relatedContents)
        );

        $fields = [];
        foreach ($relatedContents as $relatedContent) {
            $contentTypeId = $relatedContent->versionInfo->contentInfo->contentTypeId;

            $relatedFields = $this->doMapFields($relatedContent, $contentTypes[$contentTypeId], $languageCode, $maxDepth, $depth);
            foreach ($relatedFields as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns field name base on given depth.
     */
    private function getIndexFieldName(int $depth): string
    {
        if ($depth === 0) {
            return self::$fieldName;
        }

        return sprintf(self::$relatedContentFieldName, $depth);
    }

    /**
     * Return index field type for the given $contentType.
     */
    private function getIndexFieldType(ContentType $contentType): TextField
    {
        $newFieldType = new TextField();
        $newFieldType->boost = $this->boostFactorProvider->getContentMetaFieldBoostFactor(
            $contentType,
            'text'
        );

        return $newFieldType;
    }
}
