<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\EmbeddingVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Embedding;
use Ibexa\Contracts\Solr\Query\EmbeddingVisitor;
use Ibexa\Solr\Query\Common\EmbeddingVisitor\Aggregate;
use Ibexa\Tests\Solr\Search\TestCase;

final class AggregateTest extends TestCase
{
    private const EXAMPLE_VISITOR_RESULT = '{!knn f=field topK=3[0,1]';

    public function testCanVisitOnSupportedEmbedding(): void
    {
        $embedding = $this->createMock(Embedding::class);

        $dispatcher = new Aggregate([
            $this->createVisitorMock($embedding, false),
            $this->createVisitorMock($embedding, true),
            $this->createVisitorMock($embedding, false),
        ]);

        self::assertTrue($dispatcher->canVisit($embedding));
    }

    public function testCanVisitOnNonSupportedEmbedding(): void
    {
        $embedding = $this->createMock(Embedding::class);

        $dispatcher = new Aggregate([
            $this->createVisitorMock($embedding, false),
            $this->createVisitorMock($embedding, false),
            $this->createVisitorMock($embedding, false),
        ]);

        self::assertFalse($dispatcher->canVisit($embedding));
    }

    public function testVisit(): void
    {
        $embedding = $this->createMock(Embedding::class);

        $visitorA = $this->createVisitorMock($embedding, false);
        $visitorB = $this->createVisitorMock($embedding, true);
        $visitorC = $this->createVisitorMock($embedding, false);

        $dispatcher = new Aggregate([$visitorA, $visitorB, $visitorC]);

        $visitorB
            ->method('visit')
            ->with($embedding, 3)
            ->willReturn(self::EXAMPLE_VISITOR_RESULT);

        self::assertEquals(
            self::EXAMPLE_VISITOR_RESULT,
            $dispatcher->visit($embedding, 3)
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Contracts\Solr\Query\EmbeddingVisitor
     */
    private function createVisitorMock(
        Embedding $embedding,
        bool $supports
    ): EmbeddingVisitor {
        $visitor = $this->createMock(EmbeddingVisitor::class);
        $visitor->method('canVisit')->with($embedding)->willReturn($supports);

        return $visitor;
    }
}
