<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\DocumentMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler;
use Ibexa\Contracts\Core\Search\Document;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;
use Ibexa\Contracts\Solr\FieldMapper\ContentTranslationFieldMapper;
use Ibexa\Contracts\Solr\FieldMapper\LocationFieldMapper;

/**
 * NativeDocumentMapper maps Solr backend documents per Content translation.
 */
class NativeDocumentMapper implements DocumentMapper
{
    /**
     * Creates a new document mapper.
     */
    public function __construct(
        private readonly ContentFieldMapper $blockFieldMapper,
        private readonly ContentTranslationFieldMapper $blockTranslationFieldMapper,
        private readonly ContentFieldMapper $contentFieldMapper,
        private readonly ContentTranslationFieldMapper $contentTranslationFieldMapper,
        private readonly LocationFieldMapper $locationFieldMapper,
        protected readonly Handler $locationHandler
    ) {
    }

    /**
     * Maps given Content to a Document.
     *
     * @return \Ibexa\Contracts\Core\Search\Document[]
     */
    public function mapContentBlock(Content $content): array
    {
        $contentInfo = $content->versionInfo->contentInfo;
        $locations = $this->locationHandler->loadLocationsByContent($contentInfo->id);
        $blockFields = $this->getBlockFields($content);
        $contentFields = $this->getContentFields($content);
        $documents = [];
        $locationFieldsMap = [];

        foreach ($locations as $location) {
            $locationFieldsMap[$location->id] = $this->getLocationFields($location);
        }

        foreach (array_keys($content->versionInfo->names) as $languageCode) {
            $blockTranslationFields = $this->getBlockTranslationFields(
                $content,
                $languageCode
            );

            $translationLocationDocuments = [];
            foreach ($locations as $location) {
                $translationLocationDocuments[] = new Document(
                    [
                        'id' => $this->generateLocationDocumentId((int)$location->id, $languageCode),
                        'fields' => array_merge(
                            $blockFields,
                            $locationFieldsMap[$location->id],
                            $blockTranslationFields
                        ),
                    ]
                );
            }

            $isMainTranslation = ($contentInfo->mainLanguageCode === $languageCode);
            $alwaysAvailable = ($isMainTranslation && $contentInfo->alwaysAvailable);
            $contentTranslationFields = $this->getContentTranslationFields(
                $content,
                $languageCode
            );

            $documents[] = new Document(
                [
                    'id' => $this->generateContentDocumentId(
                        (int)$contentInfo->id,
                        $languageCode
                    ),
                    'languageCode' => $languageCode,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isMainTranslation' => $isMainTranslation,
                    'fields' => array_merge(
                        $blockFields,
                        $contentFields,
                        $blockTranslationFields,
                        $contentTranslationFields
                    ),
                    'documents' => $translationLocationDocuments,
                ]
            );
        }

        return $documents;
    }

    /**
     * Generates the Solr backend document ID for Content object.
     *
     * Format of id is "content<content-id>lang[<language>]".
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Content's documents (there will be one document per translation).
     * The above is useful when targeting all Content's documents, without
     * the knowledge of it's translations, and thanks to "lang" string it will not
     * risk matching other documents (as was the case in EZP-26484).
     */
    public function generateContentDocumentId(int $contentId, ?string $languageCode = null): string
    {
        return strtolower("content{$contentId}lang{$languageCode}");
    }

    /**
     * Generates the Solr backend document ID for Location object.
     *
     * Format of id is "content<content-id>lang[<language>]".
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Location's documents (there will be one document per translation).
     * The above is useful when targeting all Location's documents, without
     * the knowledge of it's translations, and thanks to "lang" string it will not
     * risk matching other documents (as was the case in EZP-26484).
     */
    public function generateLocationDocumentId(int $locationId, ?string $languageCode = null): string
    {
        return strtolower("location{$locationId}lang{$languageCode}");
    }

    /**
     * Returns an array of fields for the given $content, to be added to the
     * corresponding block documents.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    private function getBlockFields(Content $content): array
    {
        $fields = [];

        if ($this->blockFieldMapper->accept($content)) {
            $fields = $this->blockFieldMapper->mapFields($content);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content and $languageCode, to be added to the
     * corresponding block documents.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    private function getBlockTranslationFields(Content $content, string $languageCode): array
    {
        $fields = [];

        if ($this->blockTranslationFieldMapper->accept($content, $languageCode)) {
            $fields = $this->blockTranslationFieldMapper->mapFields($content, $languageCode);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content, to be added to the corresponding
     * Content document.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    private function getContentFields(Content $content): array
    {
        $fields = [];

        if ($this->contentFieldMapper->accept($content)) {
            $fields = $this->contentFieldMapper->mapFields($content);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $content and $languageCode, to be added to the
     * corresponding Content document.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    private function getContentTranslationFields(Content $content, string $languageCode): array
    {
        $fields = [];

        if ($this->contentTranslationFieldMapper->accept($content, $languageCode)) {
            $fields = $this->contentTranslationFieldMapper->mapFields($content, $languageCode);
        }

        return $fields;
    }

    /**
     * Returns an array of fields for the given $location, to be added to the corresponding
     * Location document.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    private function getLocationFields(Location $location): array
    {
        $fields = [];

        if ($this->locationFieldMapper->accept($location)) {
            $fields = $this->locationFieldMapper->mapFields($location);
        }

        return $fields;
    }
}
