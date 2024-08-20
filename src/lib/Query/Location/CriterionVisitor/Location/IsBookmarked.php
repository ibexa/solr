<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

final class IsBookmarked extends CriterionVisitor
{
    private PermissionResolver $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Location\IsBookmarked
            && $criterion->operator === Criterion\Operator::EQ;
    }

    public function visit(
        Criterion $criterion,
        CriterionVisitor $subVisitor = null
    ): string {
        $userId = $this->getUserId($criterion);

        return 'location_bookmarked_user_ids_mid:"' . $userId . '"';
    }

    /**
     * @throws \Ibexa\Contracts\Core\Exception\InvalidArgumentException
     */
    private function getUserId(Criterion $criterion): int
    {
        $valueData = $criterion->valueData;
        if (!$valueData instanceof Criterion\Value\IsBookmarkedValue) {
            throw new InvalidArgumentException(
                '$criterion->valueData',
                sprintf(
                    'Is expected to be of type: "%s", got "%s"',
                    Criterion\Value\IsBookmarkedValue::class,
                    get_debug_type($valueData)
                )
            );
        }

        return $valueData->getUserId() ?? $this->permissionResolver->getCurrentUserReference()->getUserId();
    }
}
