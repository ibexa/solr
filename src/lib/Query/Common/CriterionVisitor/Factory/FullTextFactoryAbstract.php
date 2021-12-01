<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor\Factory;

use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use QueryTranslator\Languages\Galach\Generators\ExtendedDisMax;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\Tokenizer;

/**
 * Factory for FullText Criterion Visitor.
 *
 * @see \Ibexa\Solr\Query\Content\CriterionVisitor\FullText
 * @see \Ibexa\Solr\Query\Location\CriterionVisitor\FullText
 *
 * @internal
 */
abstract class FullTextFactoryAbstract
{
    /**
     * Field map.
     *
     * @var \Ibexa\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * @var \QueryTranslator\Languages\Galach\Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \QueryTranslator\Languages\Galach\Parser
     */
    protected $parser;

    /**
     * @var \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax
     */
    protected $generator;

    /**
     * @var \Ibexa\Solr\FieldMapper\IndexingDepthProvider
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
