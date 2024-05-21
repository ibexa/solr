<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SectionAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class SectionAggregationKeyMapperTest extends TestCase
{
    private const EXAMPLE_SECTION_IDS = [1, 2, 3];

    /** @var \Ibexa\Contracts\Core\Repository\SectionService|\PHPUnit\Framework\MockObject\MockObject */
    private $sectionService;

    /** @var \Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SectionAggregationKeyMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->sectionService = $this->createMock(SectionService::class);
        $this->mapper = new SectionAggregationKeyMapper($this->sectionService);
    }

    public function testMap(): void
    {
        $expectedSections = $this->configureSectionServiceMock(self::EXAMPLE_SECTION_IDS);

        self::assertEquals(
            $expectedSections,
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_SECTION_IDS
            )
        );
    }

    private function configureSectionServiceMock(iterable $sectionIds): array
    {
        $sections = [];
        foreach ($sectionIds as $i => $sectionId) {
            $section = $this->createMock(Section::class);

            $this->sectionService
                ->expects(self::at($i))
                ->method('loadSection')
                ->with($sectionId)
                ->willReturn($section);

            $sections[$sectionId] = $section;
        }

        return $sections;
    }
}
