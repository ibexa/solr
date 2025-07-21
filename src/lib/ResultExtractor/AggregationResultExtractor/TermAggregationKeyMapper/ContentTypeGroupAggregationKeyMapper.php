<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class ContentTypeGroupAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private ContentTypeService $contentTypeService
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\ContentTypeGroupTermAggregation $aggregation
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            try {
                $result[$key] = $this->contentTypeService->loadContentTypeGroup((int)$key);
            } catch (NotFoundException) {
                // Skip missing content type groups
            }
        }

        return $result;
    }
}
