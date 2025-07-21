<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation;
use RuntimeException;

/**
 * @phpstan-extends \Ibexa\Solr\Query\Common\AggregationVisitor\AbstractRangeAggregationVisitor<\Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation>
 */
final class DateMetadataRangeAggregationVisitor extends AbstractRangeAggregationVisitor
{
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof DateMetadataRangeAggregation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation $aggregation
     */
    protected function getTargetField(AbstractRangeAggregation $aggregation): string
    {
        return match ($aggregation->getType()) {
            DateMetadataRangeAggregation::PUBLISHED => 'content_publication_date_dt',
            DateMetadataRangeAggregation::MODIFIED => 'content_modification_date_dt',
            default => throw new RuntimeException("Unsupported DateMetadataRangeAggregation type {$aggregation->getType()}"),
        };
    }
}
