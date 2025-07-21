<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;

/**
 * Maps Content fulltext fields to Content document.
 */
class ContentDocumentTranslatedContentNameField extends ContentTranslationFieldMapper
{
    /**
     * Field name, untyped.
     */
    private static string $fieldName = 'meta_content__name';

    public function __construct(
        protected readonly Handler $contentTypeHandler,
        protected readonly BoostFactorProvider $boostFactorProvider
    ) {
    }

    public function accept(Content $content, string $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, string $languageCode): array
    {
        if (!isset($content->versionInfo->names[$languageCode])) {
            return [];
        }

        $contentName = $content->versionInfo->names[$languageCode];
        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        return [
            new Field(
                self::$fieldName,
                $contentName,
                new FieldType\StringField()
            ),
            new Field(
                self::$fieldName,
                $contentName,
                new FieldType\TextField(
                    [
                        'boost' => $this->boostFactorProvider->getContentMetaFieldBoostFactor(
                            $contentType,
                            'name'
                        ),
                    ]
                )
            ),
        ];
    }
}
