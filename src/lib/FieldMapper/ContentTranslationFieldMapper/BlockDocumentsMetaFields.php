<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;

/**
 * Maps meta fields to block documents (Content and Location).
 */
class BlockDocumentsMetaFields extends ContentTranslationFieldMapper
{
    public function accept(Content $content, string $languageCode): bool
    {
        return true;
    }

    public function mapFields(Content $content, string $languageCode): array
    {
        return [
            new Field(
                'meta_indexed_language_code',
                $languageCode,
                new FieldType\StringField()
            ),
            new Field(
                'meta_indexed_is_main_translation',
                ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode),
                new FieldType\BooleanField()
            ),
            new Field(
                'meta_indexed_is_main_translation_and_always_available',
                (
                    ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode) &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            ),
        ];
    }
}
