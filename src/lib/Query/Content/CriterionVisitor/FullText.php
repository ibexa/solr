<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FullText as FullTextCriterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Search\Common\FieldNameResolver;
use QueryTranslator\Languages\Galach\Generators\ExtendedDisMax;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\Tokenizer;

/**
 * Visits the FullText criterion.
 */
class FullText extends CriterionVisitor
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
     * @var int
     */
    protected $maxDepth;

    /**
     * Create from content type handler and field registry.
     *
     * @param int $maxDepth
     */
    public function __construct(
        FieldNameResolver $fieldNameResolver,
        Tokenizer $tokenizer,
        Parser $parser,
        ExtendedDisMax $generator,
        $maxDepth = 0
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->tokenizer = $tokenizer;
        $this->parser = $parser;
        $this->generator = $generator;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Get field type information.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getSearchFields(Criterion $criterion, $fieldDefinitionIdentifier)
    {
        return $this->fieldNameResolver->getFieldTypes($criterion, $fieldDefinitionIdentifier);
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof FullTextCriterion;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $tokenSequence = $this->tokenizer->tokenize($criterion->value);
        $syntaxTree = $this->parser->parse($tokenSequence);

        $options = [];
        if ($criterion->fuzziness < 1) {
            $options['fuzziness'] = $criterion->fuzziness;
        }

        $queryString = $this->generator->generate($syntaxTree, $options);
        $queryStringEscaped = $this->escapeQuote($queryString);
        $queryFields = $this->getQueryFields($criterion);

        return "{!edismax v='{$queryStringEscaped}' qf='{$queryFields}' uf=-*}";
    }

    private function getQueryFields(Criterion $criterion)
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $queryFields = ['meta_content__text_t'];

        for ($i = 1; $i <= $this->maxDepth; ++$i) {
            $queryFields[] = "meta_related_content_{$i}__text_t^{$this->getBoostFactorForRelatedContent($i)}";
        }

        foreach ($criterion->boost as $field => $boost) {
            $searchFields = $this->getSearchFields($criterion, $field);

            foreach ($searchFields as $name => $fieldType) {
                $queryFields[] = "{$name}^{$boost}";
            }
        }

        return implode(' ', $queryFields);
    }

    /**
     * Returns boost factor for the related content.
     */
    private function getBoostFactorForRelatedContent(int $depth): float
    {
        return 1.0 / pow(2.0, $depth);
    }
}

class_alias(FullText::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\FullText');
