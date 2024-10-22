<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Tests\Solr\Search\Query\Utils\Stub\TestCriterion;
use PHPUnit\Framework\TestCase;

abstract class BaseCriterionVisitorTestCase extends TestCase
{
    abstract protected function getVisitor(): CriterionVisitor;

    abstract protected function getSupportedCriterion(): Criterion;

    /**
     * @return iterable<array{
     *     string,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
     * }>
     */
    abstract protected function provideDataForTestVisit(): iterable;

    /**
     * @dataProvider provideDataForTestCanVisit
     */
    public function testCanVisit(
        bool $expected,
        Criterion $criterion
    ): void {
        self::assertSame(
            $expected,
            $this->getVisitor()->canVisit($criterion)
        );
    }

    /**
     * @return iterable<array{
     *     bool,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
     * }>
     */
    public function provideDataForTestCanVisit(): iterable
    {
        yield 'Not supported criterion' => [
            false,
            new TestCriterion(),
        ];

        yield 'Supported criterion' => [
            true,
            $this->getSupportedCriterion(),
        ];
    }

    /**
     * @dataProvider provideDataForTestVisit
     */
    public function testVisit(
        string $expectedQuery,
        Criterion $criterion
    ): void {
        self::assertSame(
            $expectedQuery,
            $this->getVisitor()->visit($criterion)
        );
    }
}
