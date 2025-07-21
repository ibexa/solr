<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\UpdateSerializer;

use Ibexa\Contracts\Core\Search\Document;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Core\Search\Common\FieldNameGenerator;
use Ibexa\Core\Search\Common\FieldValueMapper;

/**
 * @internal
 */
abstract class UpdateSerializer
{
    public function __construct(
        protected readonly FieldValueMapper $fieldValueMapper,
        protected readonly FieldNameGenerator $nameGenerator
    ) {
    }

    /**
     * Returns a 'dummy' document.
     *
     * This is intended to be indexed as nested document of Content, in order to enforce
     * document block when Content does not have other nested documents (Locations).
     * Not intended to be matched or returned as a search result.
     *
     * For more info see:
     *
     * @see http://grokbase.com/t/lucene/solr-user/14chqr73nv/converting-to-parent-child-block-indexing
     * @see https://issues.apache.org/jira/browse/SOLR-5211
     */
    protected function getNestedDummyDocument(string $id): Document
    {
        return new Document(
            [
                'id' => $id . '_nested_dummy',
                'fields' => [
                    new Field(
                        'document_type',
                        'nested_dummy',
                        new IdentifierField()
                    ),
                ],
            ]
        );
    }
}
