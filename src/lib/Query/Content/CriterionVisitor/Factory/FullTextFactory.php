<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Content\CriterionVisitor\Factory;

use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;
use Ibexa\Solr\Query\Content\CriterionVisitor\FullText;
use QueryTranslator\Languages\Galach\Generators\ExtendedDisMax;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\Tokenizer;

/**
 * Factory for FullText Criterion Visitor.
 *
 * @see \Ibexa\Solr\Query\Content\CriterionVisitor\FullText
 *
 * @internal
 */
final readonly class FullTextFactory
{
    /**
     * Create from content type handler and field registry.
     */
    public function __construct(
        private FieldNameResolver $fieldNameResolver,
        private Tokenizer $tokenizer,
        private Parser $parser,
        private ExtendedDisMax $generator,
        private IndexingDepthProvider $indexingDepthProvider
    ) {
    }

    /**
     * Create FullText Criterion Visitor.
     */
    public function createCriterionVisitor(): FullText
    {
        return new FullText(
            $this->fieldNameResolver,
            $this->tokenizer,
            $this->parser,
            $this->generator,
            $this->indexingDepthProvider->getMaxDepth()
        );
    }
}
