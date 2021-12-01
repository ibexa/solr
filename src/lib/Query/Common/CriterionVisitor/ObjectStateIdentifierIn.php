<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

class ObjectStateIdentifierIn extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return
            $criterion instanceof Criterion\ObjectStateIdentifier &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $target = $criterion->target ?? '*';

        return sprintf(
            '(%s)',
            implode(
                ' OR ',
                array_map(
                    function (string $value) use ($target) {
                        return sprintf(
                            'content_object_state_identifiers_ms:%s',
                            $this->escapeExpressions("{$target}:{$value}", true)
                        );
                    },
                    (array)$criterion->value
                )
            )
        );
    }
}

class_alias(ObjectStateIdentifierIn::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\ObjectStateIdentifierIn');
