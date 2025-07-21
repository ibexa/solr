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
     * Create from content type handler and field registry.
     */
    public function __construct(
        protected readonly FieldNameResolver $fieldNameResolver,
        protected readonly Tokenizer $tokenizer,
        protected readonly Parser $parser,
        protected readonly ExtendedDisMax $generator,
        protected readonly IndexingDepthProvider $indexingDepthProvider
    ) {
    }

    /**
     * Create FullText Criterion Visitor.
     */
    abstract public function createCriterionVisitor(): CriterionVisitor;
}
