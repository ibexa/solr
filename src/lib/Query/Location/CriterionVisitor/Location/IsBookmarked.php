<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use LogicException;

final class IsBookmarked extends CriterionVisitor
{
    private const string SEARCH_FIELD = 'location_bookmarked_user_ids_mid';

    public function __construct(
        private readonly PermissionResolver $permissionResolver
    ) {
    }

    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\Location\IsBookmarked
            && $criterion->operator === Criterion\Operator::EQ;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsBookmarked $criterion
     */
    public function visit(
        CriterionInterface $criterion,
        ?CriterionVisitor $subVisitor = null
    ): string {
        if (!is_array($criterion->value)) {
            throw new LogicException(sprintf(
                'Expected %s Criterion value to be an array, received %s',
                Criterion\Location\IsBookmarked::class,
                get_debug_type($criterion->value),
            ));
        }

        $userId = $this->permissionResolver
            ->getCurrentUserReference()
            ->getUserId();

        $query = self::SEARCH_FIELD . ':"' . $userId . '"';

        if (!$criterion->value[0]) {
            $query = 'NOT ' . $query;
        }

        return $query;
    }
}
