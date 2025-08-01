<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor;

final class AggregationResultExtractorTestUtils
{
    public const array EXAMPLE_LANGUAGE_FILTER = [
        'languageCode' => 'eng-GB',
        'useAlwaysAvailable' => false,
    ];

    private function __construct()
    {
        /* This class shouldn't be instantiated */
    }
}
