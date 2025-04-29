<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler as LocationHandler;
use Ibexa\Contracts\Core\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use Ibexa\Contracts\Core\Persistence\Content\Section\Handler as SectionHandler;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;

/**
 * Maps base Content related fields to block document (Content and Location).
 */
class BlockDocumentsBaseContentFields extends ContentFieldMapper
{
    protected Handler $locationHandler;

    protected ContentTypeHandler $contentTypeHandler;

    protected ObjectStateHandler $objectStateHandler;

    protected SectionHandler $sectionHandler;

    public function __construct(
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    ) {
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
    }

    public function accept(Content $content): bool
    {
        return true;
    }

    public function mapFields(Content $content): array
    {
        $versionInfo = $content->versionInfo;
        $contentInfo = $content->versionInfo->contentInfo;

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $contentInfo->ownerId;

        $section = $this->sectionHandler->load($contentInfo->sectionId);
        $contentType = $this->contentTypeHandler->load($contentInfo->contentTypeId);

        return [
            new Field(
                'content_id',
                $contentInfo->id,
                new FieldType\IdentifierField()
            ),
            // explicit integer representation to allow sorting
            new Field(
                'content_id_normalized',
                $contentInfo->id,
                new FieldType\IntegerField()
            ),
            new Field(
                'content_type_id',
                $contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_version_no',
                $versionInfo->versionNo,
                new FieldType\IntegerField()
            ),
            new Field(
                'content_version_status',
                $versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_name',
                $contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_version_creator_user_id',
                $versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_owner_user_id',
                $contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_section_id',
                $contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_remote_id',
                $contentInfo->remoteId,
                new FieldType\RemoteIdentifierField()
            ),
            new Field(
                'content_modification_date',
                $contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_publication_date',
                $contentInfo->publicationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_language_codes',
                $versionInfo->languageCodes,
                new FieldType\MultipleStringField()
            ),
            new Field(
                'content_language_codes_raw',
                $versionInfo->languageCodes,
                new FieldType\MultipleIdentifierField(['raw' => true])
            ),
            new Field(
                'content_main_language_code',
                $contentInfo->mainLanguageCode,
                new FieldType\StringField()
            ),
            new Field(
                'content_always_available',
                $contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'content_owner_user_group_ids',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_section_identifier',
                $section->identifier,
                new FieldType\IdentifierField(['raw' => true])
            ),
            new Field(
                'content_section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_type_group_ids',
                $contentType->groupIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_type_is_container',
                $contentType->isContainer,
                new FieldType\BooleanField()
            ),
            new Field(
                'content_object_state_ids',
                $this->getObjectStateIds($contentInfo->id),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'content_object_state_identifiers',
                $this->getObjectStateIdentifiers($contentInfo->id),
                new FieldType\MultipleStringField()
            ),
        ];
    }

    /**
     * Returns an array of object state ids of a Content with given $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getObjectStateIds($contentId): array
    {
        $objectStateIds = [];

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            try {
                $objectStateIds[] = $this->objectStateHandler->getContentState(
                    $contentId,
                    $objectStateGroup->id
                )->id;
            } catch (NotFoundException $e) {
                // // Ignore empty object state groups
            }
        }

        return $objectStateIds;
    }

    /**
     * @return string[]
     */
    protected function getObjectStateIdentifiers(int $contentId): array
    {
        $identifiers = [];

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $identifiers[] = sprintf(
                '%s:%s',
                $objectStateGroup->identifier,
                $this->objectStateHandler->getContentState(
                    $contentId,
                    $objectStateGroup->id
                )->identifier
            );
        }

        return $identifiers;
    }

    /**
     * Returns Content ids of all ancestor Locations of all Locations
     * of a Content with given $contentId.
     *
     * Used to determine user groups of a user with $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getAncestorLocationsContentIds($contentId)
    {
        $locations = $this->locationHandler->loadLocationsByContent($contentId);
        $ancestorLocationContentIds = [];
        $ancestorLocationIds = [];

        foreach ($locations as $location) {
            $locationIds = explode('/', trim($location->pathString, '/'));
            // Remove Location of Content with $contentId
            array_pop($locationIds);
            // Remove Root Location id (id==1 in legacy DB)
            array_shift($locationIds);

            $ancestorLocationIds = array_merge($ancestorLocationIds, $locationIds);
        }

        foreach (array_unique($ancestorLocationIds) as $locationId) {
            $location = $this->locationHandler->load($locationId);

            $ancestorLocationContentIds[$location->contentId] = true;
        }

        return array_keys($ancestorLocationContentIds);
    }
}
