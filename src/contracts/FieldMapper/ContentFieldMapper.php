<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\Solr\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content as SPIContent;

/**
 * Base class for Content document field mapper.
 *
 * Content document field mapper maps Content to the search fields for Content document.
 */
abstract class ContentFieldMapper
{
    /**
     * Indicates if the mapper accepts the given $content for mapping.
     *
     * @return bool
     */
    abstract public function accept(SPIContent $content);

    /**
     * Maps given $content to an array of search fields.
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    abstract public function mapFields(SPIContent $content);
}

class_alias(ContentFieldMapper::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentFieldMapper');
