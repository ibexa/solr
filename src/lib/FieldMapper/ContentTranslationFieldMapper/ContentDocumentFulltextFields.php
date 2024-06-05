<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\Type as ContentType;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
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
     *
     * @var string
     */
    private static $fieldName = 'meta_content__text';

    /**
     * Field of related content name, untyped.
     *
     * @var string
     */
    private static $relatedContentFieldName = 'meta_related_content_%d__text';

    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \Ibexa\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * @var \Ibexa\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * @var \Ibexa\Solr\FieldMapper\BoostFactorProvider
     */
    protected $boostFactorProvider;

    /**
     * @var \Ibexa\Solr\FieldMapper\IndexingDepthProvider
     */
    protected $indexingDepthProvider;

    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        ContentHandler $contentHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        BoostFactorProvider $boostFactorProvider,
        IndexingDepthProvider $indexingDepthProvider
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentHandler = $contentHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->boostFactorProvider = $boostFactorProvider;
        $this->indexingDepthProvider = $indexingDepthProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Content $content, $languageCode)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFields(Content $content, $languageCode)
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
     * @param string $languageCode
     * @param int $maxDepth
     * @param int $depth
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function doMapFields(Content $content, ContentType $contentType, $languageCode, $maxDepth, $depth = 0)
    {
        $fields = [];

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                $fieldType = $this->fieldRegistry->getType($field->type);
                $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    if (!$indexField->type instanceof FieldType\FullTextField || !$fieldDefinition->isSearchable) {
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
     * @param int $depth
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function doMapRelatedFields(Content $sourceContent, $languageCode, $maxDepth, $depth)
    {
        $relations = $this->contentHandler->loadRelations($sourceContent->versionInfo->contentInfo->id);

        $relatedContents = $this->contentHandler->loadContentList(
            array_map(static function (Content\Relation $relation) {
                return $relation->destinationContentId;
            }, $relations)
        );

        $contentTypes = $this->contentTypeHandler->loadContentTypeList(
            array_map(static function (Content $content) {
                return $content->versionInfo->contentInfo->contentTypeId;
            }, $relatedContents)
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
     *
     * @return \Ibexa\Contracts\Core\Search\FieldType
     */
    private function getIndexFieldType(ContentType $contentType)
    {
        $newFieldType = new FieldType\TextField();
        $newFieldType->boost = $this->boostFactorProvider->getContentMetaFieldBoostFactor(
            $contentType,
            'text'
        );

        return $newFieldType;
    }
}
