<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Search\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\FieldType\StringField;
use Ibexa\Core\FieldType\TextLine\SearchField;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Solr\Query\Common\QueryTranslator\Generator\WordVisitor;
use Ibexa\Solr\Query\Content\CriterionVisitor\FullText;
use Ibexa\Tests\Solr\Search\TestCase;
use QueryTranslator\Languages\Galach\Generators;
use QueryTranslator\Languages\Galach\Parser;
use QueryTranslator\Languages\Galach\TokenExtractor\Text;
use QueryTranslator\Languages\Galach\Tokenizer;

/**
 * Test case for FullText criterion visitor.
 *
 * @covers \Ibexa\Solr\Query\Content\CriterionVisitor\FullText
 */
class FullTextTest extends TestCase
{
    protected function getFullTextCriterionVisitor(array $fieldTypes = [], int $maxDepth = 0)
    {
        $fieldNames = array_keys($fieldTypes);
        $fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldTypes'])
            ->getMock();

        $fieldNameResolver
            ->expects(self::any())
            ->method('getFieldTypes')
            ->with(
                self::isInstanceOf(Criterion::class),
                self::isType('string')
            )
            ->willReturn(
                $fieldTypes
            );

        /** @var \Ibexa\Core\Search\Common\FieldNameResolver $fieldNameResolver */
        return new FullText(
            $fieldNameResolver,
            $this->getTokenizer(),
            $this->getParser(),
            $this->getGenerator(),
            $maxDepth
        );
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Tokenizer
     */
    protected function getTokenizer()
    {
        return new Tokenizer(
            new Text()
        );
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Parser
     */
    protected function getParser()
    {
        return new Parser();
    }

    /**
     * @return \QueryTranslator\Languages\Galach\Generators\ExtendedDisMax
     */
    protected function getGenerator()
    {
        return new Generators\ExtendedDisMax(
            new Generators\Common\Aggregate(
                [
                    new Generators\Lucene\Common\Group(),
                    new Generators\Lucene\Common\LogicalAnd(),
                    new Generators\Lucene\Common\LogicalNot(),
                    new Generators\Lucene\Common\LogicalOr(),
                    new Generators\Lucene\Common\Mandatory(),
                    new Generators\Lucene\Common\Prohibited(),
                    new Generators\Lucene\Common\Phrase(),
                    new Generators\Lucene\Common\Query(),
                    new Generators\Lucene\Common\Tag(),
                    new WordVisitor(),
                    new Generators\Lucene\Common\User(),
                ]
            )
        );
    }

    public function testVisitSimple()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');

        self::assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitSimpleMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');

        self::assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;

        self::assertEquals(
            "{!edismax v='Hello~0.5' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');
        $criterion->fuzziness = .5;

        self::assertEquals(
            "{!edismax v='Hello~0.5 World~0.5' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoost()
    {
        $ftTextLine = new SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            [
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            ]
        );

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = ['title' => 2];

        self::assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostMultipleWords()
    {
        $ftTextLine = new SearchField();
        $visitor = $this->getFullTextCriterionVisitor(
            [
                'title_1_s' => $ftTextLine,
                'title_2_s' => $ftTextLine,
            ]
        );

        $criterion = new Criterion\FullText('Hello World');
        $criterion->boost = ['title' => 2];

        self::assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostUnknownField()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = [
            'unknown_field' => 2,
        ];

        self::assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostUnknownFieldMultipleWords()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello World');
        $criterion->boost = [
            'unknown_field' => 2,
        ];

        self::assertEquals(
            "{!edismax v='Hello World' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoost()
    {
        $stringField = new StringField();
        $visitor = $this->getFullTextCriterionVisitor(
            [
                'title_1_s' => $stringField,
                'title_2_s' => $stringField,
            ]
        );
        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;
        $criterion->boost = ['title' => 2];

        self::assertEquals(
            "{!edismax v='Hello~0.5' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoostMultipleWords()
    {
        $stringField = new StringField();
        $visitor = $this->getFullTextCriterionVisitor(
            [
                'title_1_s' => $stringField,
                'title_2_s' => $stringField,
            ]
        );
        $criterion = new Criterion\FullText('Hello World');
        $criterion->fuzziness = .5;
        $criterion->boost = ['title' => 2];

        self::assertEquals(
            "{!edismax v='Hello~0.5 World~0.5' qf='meta_content__text_t title_1_s^2 title_2_s^2' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitErrorCorrection()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('OR Hello && (and goodbye)) AND OR AND "as NOT +always');

        self::assertEquals(
            "{!edismax v='Hello AND (and goodbye) as +always' qf='meta_content__text_t' uf=-*}",
            $visitor->visit($criterion)
        );
    }

    public function testVisitWithRelated()
    {
        $visitor = $this->getFullTextCriterionVisitor([], 3);

        $criterion = new Criterion\FullText('Hello');

        self::assertEquals(
            "{!edismax v='Hello' qf='meta_content__text_t meta_related_content_1__text_t^0.5 meta_related_content_2__text_t^0.25 meta_related_content_3__text_t^0.125' uf=-*}",
            $visitor->visit($criterion)
        );
    }
}
