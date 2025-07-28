<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ContentTypeGroupAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContentTypeGroupAggregationKeyMapperTest extends TestCase
{
    private const array EXAMPLE_CONTENT_TYPE_GROUPS_IDS = ['1', '2', '3'];

    private ContentTypeService&MockObject $contentTypeService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    public function testMap(): void
    {
        $expectedContentTypesGroups = $this->createExpectedLanguages();

        $mapper = new ContentTypeGroupAggregationKeyMapper($this->contentTypeService);

        self::assertEquals(
            $expectedContentTypesGroups,
            $mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_CONTENT_TYPE_GROUPS_IDS
            )
        );
    }

    private function createContentTypeGroupWithIds(int $id): ContentTypeGroup
    {
        $contentTypeGroup = $this->createMock(ContentTypeGroup::class);
        $contentTypeGroup->method('__get')->with('id')->willReturn($id);

        return $contentTypeGroup;
    }

    /**
     * @return array<int, \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup>
     */
    private function createExpectedLanguages(): array
    {
        $expectedContentTypesGroups = [];

        $map = [];
        foreach (self::EXAMPLE_CONTENT_TYPE_GROUPS_IDS as $id) {
            $contentTypeGroup = $this->createContentTypeGroupWithIds((int)$id);
            $expectedContentTypesGroups[$id] = $contentTypeGroup;

            $map[] = [(int)$id, [], $contentTypeGroup];
        }

        $this->contentTypeService
            ->method('loadContentTypeGroup')
            ->willReturnMap($map);

        return $expectedContentTypesGroups;
    }
}
