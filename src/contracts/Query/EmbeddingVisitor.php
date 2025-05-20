<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Embedding;

abstract class EmbeddingVisitor
{
    abstract public function canVisit(Embedding $embedding): bool;

    abstract public function visit(Embedding $embedding, int $limit): string;
}
