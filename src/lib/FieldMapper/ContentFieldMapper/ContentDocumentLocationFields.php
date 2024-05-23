<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\FieldMapper\ContentFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler as LocationHandler;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;

/**
 * Maps Location related fields to a Content document.
 */
class ContentDocumentLocationFields extends ContentFieldMapper
{
    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    public function __construct(LocationHandler $locationHandler)
    {
        $this->locationHandler = $locationHandler;
    }

    public function accept(Content $content)
    {
        return true;
    }

    public function mapFields(Content $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationData = [];
        $fields = [];

        foreach ($locations as $location) {
            $locationData['ids'][] = $location->id;
            $locationData['parent_ids'][] = $location->parentId;
            $locationData['remote_ids'][] = $location->remoteId;
            $locationData['path_strings'][] = $location->pathString;

            $ancestorsIds = $this->getAncestors($location);
            foreach ($ancestorsIds as $ancestorId) {
                if (!in_array($ancestorId, $locationData['ancestors'] ?? [])) {
                    $locationData['ancestors'][] = $ancestorId;
                }
            }

            if ($location->id == $content->versionInfo->contentInfo->mainLocationId) {
                $mainLocation = $location;
            }

            if (!$location->hidden && !$location->invisible) {
                $isSomeLocationVisible = true;
            }
        }

        if (!empty($locationData)) {
            $fields[] = new Field(
                'location_id',
                $locationData['ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_parent_id',
                $locationData['parent_ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_remote_id',
                $locationData['remote_ids'],
                new FieldType\MultipleRemoteIdentifierField()
            );
            $fields[] = new Field(
                'location_path_string',
                $locationData['path_strings'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_ancestors',
                $locationData['ancestors'],
                new FieldType\MultipleIdentifierField()
            );
        }

        if ($mainLocation !== null) {
            $fields[] = new Field(
                'main_location',
                $mainLocation->id,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new FieldType\RemoteIdentifierField()
            );
            $fields[] = new Field(
                'main_location_visible',
                !$mainLocation->hidden && !$mainLocation->invisible,
                new FieldType\BooleanField()
            );
            $fields[] = new Field(
                'main_location_path',
                $mainLocation->pathString,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_depth',
                $mainLocation->depth,
                new FieldType\IntegerField()
            );
            $fields[] = new Field(
                'main_location_priority',
                $mainLocation->priority,
                new FieldType\IntegerField()
            );
        }

        $fields[] = new Field(
            'location_visible',
            $isSomeLocationVisible,
            new FieldType\BooleanField()
        );

        return $fields;
    }

    private function getAncestors(Location $location): array
    {
        $ancestorsIds = explode('/', trim($location->pathString, '/'));
        // Remove $location->id from ancestors
        array_pop($ancestorsIds);

        return $ancestorsIds;
    }
}

class_alias(ContentDocumentLocationFields::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper\ContentDocumentLocationFields');
