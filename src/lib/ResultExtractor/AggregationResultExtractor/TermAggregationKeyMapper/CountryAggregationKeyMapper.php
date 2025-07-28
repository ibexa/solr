<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class CountryAggregationKeyMapper implements TermAggregationKeyMapper
{
    /**
     * @param array<string, array{Name: string, Alpha2: string, Alpha3: string, IDC: string}> $countriesInfo Array of countries data
     */
    public function __construct(
        private array $countriesInfo
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation $aggregation
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->mapKey($aggregation, $key);
        }

        return $results;
    }

    private function mapKey(Aggregation $aggregation, int $key): ?string
    {
        $countryInfo = $this->findCountryInfoByIDC($key);

        if ($countryInfo === null) {
            return null;
        }

        return match ($aggregation->getType()) {
            CountryTermAggregation::TYPE_NAME => $countryInfo['Name'],
            CountryTermAggregation::TYPE_IDC => $countryInfo['IDC'],
            CountryTermAggregation::TYPE_ALPHA_2 => $countryInfo['Alpha2'],
            CountryTermAggregation::TYPE_ALPHA_3 => $countryInfo['Alpha3'],
            default => null,
        };
    }

    private function findCountryInfoByIDC(int $idc): ?array
    {
        foreach ($this->countriesInfo as $countryInfo) {
            if ((int)$countryInfo['IDC'] === $idc) {
                return $countryInfo;
            }
        }

        return null;
    }
}
