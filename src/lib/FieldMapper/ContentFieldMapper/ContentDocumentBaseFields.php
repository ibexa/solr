<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;

/**
 * Maps base Content related fields to a Content document.
 */
class ContentDocumentBaseFields extends ContentFieldMapper
{
    public function accept(Content $content): bool
    {
        return true;
    }

    public function mapFields(Content $content): array
    {
        return [
            new Field(
                'document_type',
                DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT,
                new FieldType\IdentifierField()
            ),
        ];
    }
}
