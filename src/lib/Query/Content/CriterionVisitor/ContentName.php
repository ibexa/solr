<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use QueryTranslator\Languages\Galach\Generators\ExtendedDisMax;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Tokenizing;

/**
 * @internal
 */
final class ContentName extends CriterionVisitor
{
    private Tokenizing $tokenizer;

    private Parser $parser;

    private ExtendedDisMax $generator;

    public function __construct(
        Tokenizing $tokenizer,
        Parser $parser,
        ExtendedDisMax $generator
    ) {
        $this->tokenizer = $tokenizer;
        $this->parser = $parser;
        $this->generator = $generator;
    }

    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\ContentName
            && $criterion->operator === Criterion\Operator::LIKE;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        /** @var string $value */
        $value = $criterion->value;
        $tokenSequence = $this->tokenizer->tokenize($value);
        $syntaxTree = $this->parser->parse($tokenSequence);

        $queryString = $this->generator->generate($syntaxTree);
        $searchField = 'meta_content__name_s';

        return "{!edismax v='{$this->escapeQuote($queryString)}' qf='{$searchField}' uf=-*}";
    }
}
