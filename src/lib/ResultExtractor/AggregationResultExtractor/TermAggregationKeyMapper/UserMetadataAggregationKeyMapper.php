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

final class UserMetadataAggregationKeyMapper implements TermAggregationKeyMapper
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation $aggregation
     * @param string[] $keys
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
            } catch (NotFoundException | UnauthorizedException $e) {
                // Skip missing users / user groups
            }
        }

        return $results;
    }

    private function resolveKeyLoader(Aggregation $aggregation): callable
    {
        $type = $aggregation->getType();
        switch ($type) {
            case UserMetadataTermAggregation::OWNER:
            case UserMetadataTermAggregation::MODIFIER:
                return [$this->userService, 'loadUser'];
            case UserMetadataTermAggregation::GROUP:
                return [$this->userService, 'loadUserGroup'];
        }

        throw new InvalidArgumentException(sprintf(
            'Expected one of: "%s". Received "%s"',
            implode('", "', [
                UserMetadataTermAggregation::OWNER, UserMetadataTermAggregation::MODIFIER, UserMetadataTermAggregation::GROUP,
            ]),
            $type,
        ));
    }
}
