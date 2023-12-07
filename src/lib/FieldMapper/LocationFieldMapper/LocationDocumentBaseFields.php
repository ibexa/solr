<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\FieldMapper\LocationFieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Contracts\Solr\FieldMapper\LocationFieldMapper;

/**
 * Maps base Location related fields to a Location document.
 */
class LocationDocumentBaseFields extends LocationFieldMapper
{
    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Handler
     */
    protected $contentHandler;

    public function __construct(ContentHandler $contentHandler)
    {
        $this->contentHandler = $contentHandler;
    }

    public function accept(Location $location)
    {
        return true;
    }

    public function mapFields(Location $location)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($location->contentId);

        return [
            new Field(
                'location',
                $location->id,
                new FieldType\IdentifierField()
            ),
            // explicit integer representation to allow sorting
            new Field(
                'location_id_normalized',
                $location->id,
                new FieldType\IntegerField()
            ),
            new Field(
                'document_type',
                DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_LOCATION,
                new FieldType\IdentifierField()
            ),
            new Field(
                'priority',
                $location->priority,
                new FieldType\IntegerField()
            ),
            new Field(
                'hidden',
                $location->hidden,
                new FieldType\BooleanField()
            ),
            new Field(
                'invisible',
                $location->invisible,
                new FieldType\BooleanField()
            ),
            new Field(
                'remote_id',
                $location->remoteId,
                new FieldType\RemoteIdentifierField()
            ),
            new Field(
                'parent_id',
                $location->parentId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'path_string',
                $location->pathString,
                new FieldType\IdentifierField()
            ),
            new Field(
                'location_ancestors',
                $this->getAncestors($location),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'depth',
                $location->depth,
                new FieldType\IntegerField()
            ),
            new Field(
                'sort_field',
                $location->sortField,
                new FieldType\IdentifierField()
            ),
            new Field(
                'sort_order',
                $location->sortOrder,
                new FieldType\IdentifierField()
            ),
            new Field(
                'is_main_location',
                ($location->id == $contentInfo->mainLocationId),
                new FieldType\BooleanField()
            ),
        ];
    }

    private function getAncestors(Location $location): array
    {
        $ancestorsIds = explode('/', trim($location->pathString, '/'));
        // Remove $location->id from ancestors
        array_pop($ancestorsIds);

        return $ancestorsIds;
    }
}

class_alias(LocationDocumentBaseFields::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\LocationFieldMapper\LocationDocumentBaseFields');
