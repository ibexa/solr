<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\ResultExtractor;

use Ibexa\Contracts\Core\Persistence\Content\ContentInfo;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Solr\ResultExtractor;
use RuntimeException;
use stdClass;

/**
 * The Native Result Extractor extracts the value object from the data
 * returned by the Solr backend.
 */
class NativeResultExtractor extends ResultExtractor
{
    /**
     * @throws \RuntimeException
     */
    public function extractHit(stdClass $hit): ContentInfo|Location
    {
        if ($hit->document_type_id === 'content') {
            return $this->extractContentInfoFromHit($hit);
        }

        if ($hit->document_type_id === 'location') {
            return $this->extractLocationFromHit($hit);
        }

        throw new RuntimeException("Could not extract: document of type '{$hit->document_type_id}' is not handled.");
    }

    /**
     * @param object{
     *     document_type_id: string,
     *     content_id_id: int|string,
     *     content_name_s: string,
     *     content_type_id_id: int|string,
     *     content_section_id_id: int|string,
     *     content_version_no_i: int,
     *     content_owner_user_id_id: int|string,
     *     content_modification_date_dt: string,
     *     content_publication_date_dt: string,
     *     content_always_available_b: bool,
     *     content_remote_id_id: string,
     *     content_main_language_code_s: string,
     *     main_location_id?: int|string
     * }&\stdClass $hit
     */
    protected function extractContentInfoFromHit(stdClass $hit): ContentInfo
    {
        $contentInfo = new ContentInfo(
            [
                'id' => (int)$hit->content_id_id,
                'name' => $hit->content_name_s,
                'contentTypeId' => (int)$hit->content_type_id_id,
                'sectionId' => (int)$hit->content_section_id_id,
                'currentVersionNo' => $hit->content_version_no_i,
                'ownerId' => (int)$hit->content_owner_user_id_id,
                'modificationDate' => strtotime((string) $hit->content_modification_date_dt),
                'publicationDate' => strtotime((string) $hit->content_publication_date_dt),
                'alwaysAvailable' => $hit->content_always_available_b,
                'remoteId' => $hit->content_remote_id_id,
                'mainLanguageCode' => $hit->content_main_language_code_s,
                'status' => ContentInfo::STATUS_PUBLISHED,
            ]
        );

        if (isset($hit->main_location_id)) {
            $contentInfo->mainLocationId = (int)$hit->main_location_id;
        }

        return $contentInfo;
    }

    /**
     * @param object{
     *     location_id: int|string,
     *     priority_i: int,
     *     hidden_b: bool,
     *     invisible_b: bool,
     *     remote_id_id: string,
     *     content_id_id: int|string,
     *     parent_id_id: int|string,
     *     path_string_id: string,
     *     depth_i: int,
     *     sort_field_id: int|string,
     *     sort_order_id: int|string
     * }&\stdClass $hit
     */
    protected function extractLocationFromHit(stdClass $hit): Location
    {
        return new Location(
            [
                'id' => (int)$hit->location_id,
                'priority' => $hit->priority_i,
                'hidden' => $hit->hidden_b,
                'invisible' => $hit->invisible_b,
                'remoteId' => $hit->remote_id_id,
                'contentId' => (int)$hit->content_id_id,
                'parentId' => (int)$hit->parent_id_id,
                'pathString' => $hit->path_string_id,
                'depth' => $hit->depth_i,
                'sortField' => (int)$hit->sort_field_id,
                'sortOrder' => (int)$hit->sort_order_id,
            ]
        );
    }
}
