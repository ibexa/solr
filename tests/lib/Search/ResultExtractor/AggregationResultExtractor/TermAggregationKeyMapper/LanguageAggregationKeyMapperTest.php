<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LanguageAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class LanguageAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_CODES = [];

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService|\PHPUnit\Framework\MockObject\MockObject */
    private $languageService;

    /** @var \Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\CountryAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
        $this->mapper = new LanguageAggregationKeyMapper($this->languageService);
    }

    public function testMap(): void
    {
        $expectedLanguages = $this->configureLanguageServiceMock(self::EXAMPLE_LANGUAGE_CODES);

        $this->languageService
            ->method('loadLanguageListByCode')
            ->with(self::EXAMPLE_LANGUAGE_CODES)
            ->willReturn($expectedLanguages);

        $this->assertEquals(
            array_combine(
                self::EXAMPLE_LANGUAGE_CODES,
                $expectedLanguages
            ),
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_LANGUAGE_CODES
            )
        );
    }

    private function configureLanguageServiceMock(iterable $languageCodes): array
    {
        $languages = [];
        foreach ($languageCodes as $languageCode) {
            $language = $this->createMock(Language::class);
            $language->method('__get')->with('languageCode')->willReturn($languageCode);

            $languages[] = $languageCode;
        }

        return $languages;
    }
}

class_alias(LanguageAggregationKeyMapperTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LanguageAggregationKeyMapperTest');
