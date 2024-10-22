<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Repository\Values\User\UserReference;
use Ibexa\Solr\Query\Location\CriterionVisitor\Location\IsBookmarked;
use Ibexa\Tests\Solr\Search\Query\BaseCriterionVisitorTestCase;

/**
 * @covers \Ibexa\Solr\Query\Location\CriterionVisitor\Location\IsBookmarked
 */
final class IsBookmarkedTest extends BaseCriterionVisitorTestCase
{
    private const USER_ID = 123;

    private CriterionVisitor $visitor;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver&\PHPUnit\Framework\MockObject\MockObject */
    private PermissionResolver $permissionResolver;

    protected function setUp(): void
    {
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->visitor = new IsBookmarked($this->permissionResolver);
    }

    /**
     * @dataProvider provideDataForTestVisit
     */
    public function testVisit(
        string $expectedQuery,
        Criterion $criterion
    ): void {
        $this->mockPermissionResolverGetCurrentUserReference();

        self::assertSame(
            $expectedQuery,
            $this->visitor->visit($criterion)
        );
    }

    /**
     * @return iterable<array{
     *     string,
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
     * }>
     */
    public function provideDataForTestVisit(): iterable
    {
        yield 'Query for bookmarked locations' => [
            'location_bookmarked_user_ids_mid:"123"',
            new Criterion\Location\IsBookmarked(),
        ];

        yield 'Query for not bookmarked locations' => [
            'NOT location_bookmarked_user_ids_mid:"123"',
            new Criterion\Location\IsBookmarked(false),
        ];
    }

    private function mockPermissionResolverGetCurrentUserReference(): void
    {
        $this->permissionResolver
            ->method('getCurrentUserReference')
            ->willReturn(new UserReference(self::USER_ID));
    }

    protected function getVisitor(): CriterionVisitor
    {
        return $this->visitor;
    }

    protected function getSupportedCriterion(): Criterion
    {
        return new Criterion\Location\IsBookmarked();
    }
}
