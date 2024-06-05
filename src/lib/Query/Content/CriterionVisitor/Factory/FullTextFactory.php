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
final class FullTextFactory
{
    /**
     * Field map.
     *
     * @var \Ibexa\Core\Search\Common\FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var \QueryTranslator\Languages\Galach\Tokenizer
     */
    private $tokenizer;

    /**
     * @var \QueryTranslator\Languages\Galach\Parser
     */
    private $parser;

    /**
     * @var \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax
     */
    private $generator;

    /**
     * @var \Ibexa\Solr\FieldMapper\IndexingDepthProvider
     */
    private $indexingDepthProvider;

    /**
     * Create from content type handler and field registry.
     *
     * @param \Ibexa\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param \QueryTranslator\Languages\Galach\Tokenizer $tokenizer
     * @param \QueryTranslator\Languages\Galach\Parser $parser
     * @param \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax $generator
     * @param \Ibexa\Solr\FieldMapper\IndexingDepthProvider $indexingDepthProvider
     */
    public function __construct(
        FieldNameResolver $fieldNameResolver,
        Tokenizer $tokenizer,
        Parser $parser,
        ExtendedDisMax $generator,
        IndexingDepthProvider $indexingDepthProvider
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->tokenizer = $tokenizer;
        $this->parser = $parser;
        $this->generator = $generator;
        $this->indexingDepthProvider = $indexingDepthProvider;
    }

    /**
     * Create FullText Criterion Visitor.
     *
     * @return \Ibexa\Solr\Query\Content\CriterionVisitor\FullText
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
