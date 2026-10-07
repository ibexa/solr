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
 * @see FullText
 *
 * @internal
 */
final class FullTextFactory
{
    /**
     * Field map.
     *
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ExtendedDisMax
     */
    private $generator;

    /**
     * @var IndexingDepthProvider
     */
    private $indexingDepthProvider;

    /**
     * Create from content type handler and field registry.
     *
     * @param FieldNameResolver $fieldNameResolver
     * @param Tokenizer $tokenizer
     * @param Parser $parser
     * @param ExtendedDisMax $generator
     * @param IndexingDepthProvider $indexingDepthProvider
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
     * @return FullText
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

class_alias(FullTextFactory::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\Factory\FullTextFactory');
