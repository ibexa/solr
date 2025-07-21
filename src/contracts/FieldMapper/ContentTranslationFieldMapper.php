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
     */
    abstract public function accept(SPIContent $content, string $languageCode): bool;

    /**
     * Maps given $content for $languageCode to an array of search fields.
     *
     * @return list<\Ibexa\Contracts\Core\Search\Field>
     */
    abstract public function mapFields(SPIContent $content, string $languageCode): array;
}
