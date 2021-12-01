<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class UserMetadataAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_USER_IDS = [1, 2, 3];
    private const EXAMPLE_USER_GROUP_IDS = [1, 2, 3];

    /** @var \Ibexa\Contracts\Core\Repository\UserService|\PHPUnit\Framework\MockObject\MockObject */
    private $userService;

    /** @var \Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->mapper = new UserMetadataAggregationKeyMapper($this->userService);
    }

    /**
     * @dataProvider dataProviderForTestMapUser
     */
    public function testMapForUserKey(Aggregation $aggregation): void
    {
        $this->assertEquals(
            $this->createExpectedResultForUserKey(self::EXAMPLE_USER_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_USER_IDS,
            )
        );
    }

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

        $this->assertEquals(
            $this->createExpectedResultForUserGroupKey(self::EXAMPLE_USER_GROUP_IDS),
            $this->mapper->map(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_USER_GROUP_IDS,
            )
        );
    }

    private function createExpectedResultForUserKey(iterable $userIds): array
    {
        $users = [];
        foreach ($userIds as $i => $userId) {
            $user = $this->createMock(User::class);

            $this->userService
                ->expects($this->at($i))
                ->method('loadUser')
                ->with($userId)
                ->willReturn($user);

            $users[$userId] = $user;
        }

        return $users;
    }

    private function createExpectedResultForUserGroupKey(iterable $userGroupsIds): array
    {
        $users = [];
        foreach ($userGroupsIds as $i => $userGroupId) {
            $user = $this->createMock(UserGroup::class);

            $this->userService
                ->expects($this->at($i))
                ->method('loadUserGroup')
                ->with($userGroupId)
                ->willReturn($user);

            $users[$userGroupId] = $user;
        }

        return $users;
    }
}

class_alias(UserMetadataAggregationKeyMapperTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\UserMetadataAggregationKeyMapperTest');
