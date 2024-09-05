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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Solr\Query\Location\CriterionVisitor\Location\IsBookmarked
 */
final class IsBookmarkedTest extends TestCase
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
     * @dataProvider provideDataForTestCanVisit
     */
    public function testCanVisit(
        bool $expected,
        Criterion $criterion
    ): void {
        self::assertSame(
            $expected,
            $this->visitor->canVisit($criterion)
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
            new Criterion\ContentId(123),
        ];

        yield 'Supported criterion' => [
            true,
            new Criterion\Location\IsBookmarked(),
        ];
    }

    /**
     * @dataProvider provideDataForTestVisit
     */
    public function testVisit(
        string $expected,
        Criterion $criterion
    ): void {
        $this->mockPermissionResolverGetCurrentUserReference();

        self::assertSame(
            $expected,
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
}
