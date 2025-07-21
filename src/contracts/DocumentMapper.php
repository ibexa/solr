<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Solr;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Location;

/**
 * Mapper maps Content and Location objects to a Document objects, representing a
 * document in Solr index storage.
 *
 * Note that custom implementations might need to be accompanied by custom schema.
 */
interface DocumentMapper
{
    /**
     * Identifier of Content documents.
     */
    public const string DOCUMENT_TYPE_IDENTIFIER_CONTENT = 'content';

    /**
     * Identifier of Location documents.
     */
    public const string DOCUMENT_TYPE_IDENTIFIER_LOCATION = 'location';

    /**
     * Maps given Content and it's Locations to a collection of nested Documents,
     * one per translation.
     *
     * Each Content Document contains nested Documents representing it's Locations.
     *
     * @return list<\Ibexa\Contracts\Core\Search\Document>
     */
    public function mapContentBlock(Content $content): array;

    /**
     * Generates the Solr backend document ID for Content object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Content's documents (there will be one document per translation).
     * The above is useful when targeting all Content's documents, without
     * the knowledge of it's translations.
     */
    public function generateContentDocumentId(int $contentId, ?string $languageCode = null): string;

    /**
     * Generates the Solr backend document ID for Location object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Location's documents (there will be one document per translation).
     * The above is useful when targeting all Location's documents, without
     * the knowledge of it's Content's translations.
     */
    public function generateLocationDocumentId(int $locationId, ?string $languageCode = null): string;
}
