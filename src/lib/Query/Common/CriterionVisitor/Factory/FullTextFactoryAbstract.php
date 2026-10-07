<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor\Factory;

use Ibexa\Contracts\Solr\Query\CriterionVisitor;
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
 * @see \Ibexa\Solr\Query\Location\CriterionVisitor\FullText
 *
 * @internal
 */
abstract class FullTextFactoryAbstract
{
    /**
     * Field map.
     *
     * @var FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var ExtendedDisMax
     */
    protected $generator;

    /**
     * @var IndexingDepthProvider
     */
    protected $indexingDepthProvider;

    /**
     * Create from content type handler and field registry.
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
     */
    abstract public function createCriterionVisitor(): CriterionVisitor;
}

class_alias(FullTextFactoryAbstract::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Factory\FullTextFactoryAbstract');
