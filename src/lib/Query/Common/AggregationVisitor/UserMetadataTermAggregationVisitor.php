<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;

final class UserMetadataTermAggregationVisitor extends AbstractTermAggregationVisitor
{
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof UserMetadataTermAggregation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation $aggregation
     */
    protected function getTargetField(AbstractTermAggregation $aggregation): string
    {
        switch ($aggregation->getType()) {
            case UserMetadataTermAggregation::OWNER:
                return 'content_owner_user_id_id';
            case UserMetadataTermAggregation::GROUP:
                return 'content_owner_user_group_ids_mid';
            case UserMetadataTermAggregation::MODIFIER:
                return 'content_version_creator_user_id_id';
            default:
                throw new InvalidArgumentException(
                    '$type',
                    'Unsupported UserMetadataTermAggregation type: ' . $aggregation->getType()
                );
        }
    }
}
