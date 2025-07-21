<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserMetadataAggregationKeyMapperTest extends TestCase
{
    private const array EXAMPLE_USER_IDS = [1, 2, 3];
    private const array EXAMPLE_USER_GROUP_IDS = [1, 2, 3];

    private UserService&MockObject $userService;

    private UserMetadataAggregationKeyMapper $mapper;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->mapper = new UserMetadataAggregationKeyMapper($this->userService);
    }

    /**
     * @dataProvider dataProviderForTestMapUser
     */
    public function testMapForUserKey(UserMetadataTermAggregation $aggregation): void
    {
        self::assertEquals(
            $this->createExpectedResultForUserKey(self::EXAMPLE_USER_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                array_map('strval', self::EXAMPLE_USER_IDS)
            )
        );
    }

    /**
     * @return iterable<string, array{UserMetadataTermAggregation}>
     */
    public function dataProviderForTestMapUser(): iterable
    {
        yield UserMetadataTermAggregation::OWNER => [
            new UserMetadataTermAggregation('owner', UserMetadataTermAggregation::OWNER),
        ];

        yield UserMetadataTermAggregation::MODIFIER => [
            new UserMetadataTermAggregation('modifier', UserMetadataTermAggregation::MODIFIER),
        ];
    }

    public function testMapForUserGroup(): void
    {
        $aggregation = new UserMetadataTermAggregation('group', UserMetadataTermAggregation::GROUP);

        self::assertEquals(
            $this->createExpectedResultForUserGroupKey(self::EXAMPLE_USER_GROUP_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                array_map('strval', self::EXAMPLE_USER_GROUP_IDS)
            )
        );
    }

    /**
     * @param iterable<int> $userIds
     *
     * @return array<int, User>
     */
    private function createExpectedResultForUserKey(iterable $userIds): array
    {
        $users = [];
        foreach ($userIds as $userId) {
            $users[$userId] = $this->createMock(User::class);
        }

        $this->userService
            ->method('loadUser')
            ->willReturnCallback(static fn ($userId): \PHPUnit\Framework\MockObject\MockObject => $users[$userId] ?? throw new InvalidArgumentException('userId', "Unexpected user ID: $userId"));

        return $users;
    }

    /**
     * @param iterable<int> $userGroupIds
     *
     * @return array<int, UserGroup>
     */
    private function createExpectedResultForUserGroupKey(iterable $userGroupIds): array
    {
        $userGroups = [];
        foreach ($userGroupIds as $userGroupId) {
            $userGroups[$userGroupId] = $this->createMock(UserGroup::class);
        }

        $this->userService
            ->expects(self::any())
            ->method('loadUserGroup')
            ->willReturnCallback(static fn ($userGroupId): \PHPUnit\Framework\MockObject\MockObject => $userGroups[$userGroupId] ?? throw new InvalidArgumentException('userGroupId', "Unexpected user group ID: $userGroupId"));

        return $userGroups;
    }
}
