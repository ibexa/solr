<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\EmbeddingVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Embedding;
use Ibexa\Contracts\Solr\Query\EmbeddingVisitor;

final class Aggregate extends EmbeddingVisitor
{
    /**
     * @var iterable<\Ibexa\Contracts\Solr\Query\EmbeddingVisitor>
     */
    protected iterable $visitors = [];

    /**
     * @param \Ibexa\Contracts\Solr\Query\EmbeddingVisitor[] $visitors
     */
    public function __construct(iterable $visitors = [])
    {
        $this->visitors = $visitors;
    }

    public function canVisit(Embedding $embedding): bool
    {
        return $this->findVisitor($embedding) !== null;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException
     */
    public function visit(Embedding $embedding, int $limit): string
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($embedding)) {
                return $visitor->visit($embedding, $limit);
            }
        }

        throw new NotImplementedException('No visitor available for: ' . \get_class($embedding));
    }

    private function findVisitor(Embedding $embedding): ?EmbeddingVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($embedding)) {
                return $visitor;
            }
        }

        return null;
    }
}
