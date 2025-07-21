<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use InvalidArgumentException;

final readonly class UserMetadataAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private UserService $userService
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation $aggregation
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $loader = $this->resolveKeyLoader($aggregation);

        $results = [];
        foreach ($keys as $key) {
            try {
                $results[$key] = $loader((int)$key);
            } catch (NotFoundException | UnauthorizedException) {
                // Skip missing users / user groups
            }
        }

        return $results;
    }

    private function resolveKeyLoader(Aggregation $aggregation): callable
    {
        $type = $aggregation->getType();

        return match ($type) {
            UserMetadataTermAggregation::OWNER, UserMetadataTermAggregation::MODIFIER => $this->userService->loadUser(...),
            UserMetadataTermAggregation::GROUP => $this->userService->loadUserGroup(...),
            default => throw new InvalidArgumentException(sprintf(
                'Expected one of: "%s". Received "%s"',
                implode('", "', [
                    UserMetadataTermAggregation::OWNER, UserMetadataTermAggregation::MODIFIER, UserMetadataTermAggregation::GROUP,
                ]),
                $type,
            )),
        };
    }
}
