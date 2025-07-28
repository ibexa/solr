<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use Ibexa\Core\FieldType\Author\Author;

final class AuthorAggregationKeyMapper implements TermAggregationKeyMapper
{
    /**
     * @return \Ibexa\Core\FieldType\Author\Author[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $properties = json_decode((string) $key, true);
            if ($properties !== false) {
                $results[$key] = new Author($properties);
            }
        }

        return $results;
    }
}
