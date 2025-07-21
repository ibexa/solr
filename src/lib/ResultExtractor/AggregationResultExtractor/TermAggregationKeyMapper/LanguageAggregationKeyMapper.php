<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class LanguageAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private LanguageService $languageService
    ) {
    }

    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];

        $languages = $this->languageService->loadLanguageListByCode($keys);
        foreach ($languages as $language) {
            $result[$language->languageCode] = $language;
        }

        return $result;
    }
}
