<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\Solr\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content as SPIContent;

/**
 * Base class for Content translation document field mapper.
 *
 * Content translation document field mapper maps Content in a specific translation to the
 * search fields for Content document.
 */
abstract class ContentTranslationFieldMapper
{
    /**
     * Indicates if the mapper accepts given $content and $languageCode for mapping.
     *
     * @param string $languageCode
     *
     * @return bool
     */
    abstract public function accept(SPIContent $content, $languageCode);

    /**
     * Maps given $content for $languageCode to an array of search fields.
     *
     * @param string $languageCode
     *
     * @return \Ibexa\Contracts\Core\Search\Field[]
     */
    abstract public function mapFields(SPIContent $content, $languageCode);
}

class_alias(ContentTranslationFieldMapper::class, 'EzSystems\EzPlatformSolrSearchEngine\FieldMapper\ContentTranslationFieldMapper');
