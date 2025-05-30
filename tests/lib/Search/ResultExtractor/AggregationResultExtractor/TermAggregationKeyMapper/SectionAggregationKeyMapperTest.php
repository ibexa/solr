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
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\SectionAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SectionAggregationKeyMapperTest extends TestCase
{
    private const array EXAMPLE_SECTION_IDS = [1, 2, 3];

    private SectionService&MockObject $sectionService;

    private SectionAggregationKeyMapper $mapper;

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
        foreach ($sectionIds as $sectionId) {
            $sections[$sectionId] = $this->createMock(Section::class);
        }

        $this->sectionService
            ->method('loadSection')
            ->willReturnCallback(static function ($id) use ($sections) {
                if (isset($sections[$id])) {
                    return $sections[$id];
                }

                throw new InvalidArgumentException('id', "Unexpected section ID: $id");
            });

        return $sections;
    }
}
