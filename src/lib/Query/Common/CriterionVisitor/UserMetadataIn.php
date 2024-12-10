<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the UserMetadata criterion.
 */
class UserMetadataIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion)
    {
        return
            $criterion instanceof Criterion\UserMetadata &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserMetadata $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null)
    {
        switch ($criterion->target) {
            case Criterion\UserMetadata::MODIFIER:
                $solrField = 'content_version_creator_user_id_id';
                break;
            case Criterion\UserMetadata::OWNER:
                $solrField = 'content_owner_user_id_id';
                break;
            case Criterion\UserMetadata::GROUP:
                $solrField = 'content_owner_user_group_ids_mid';
                break;

            default:
                throw new NotImplementedException('No visitor available for target: ' . $criterion->target . ' with operator: ' . $criterion->operator);
        }

        return '(' .
            implode(
                ' OR ',
                array_map(
                    static function ($value) use ($solrField) {
                        return "{$solrField}:\"{$value}\"";
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}
