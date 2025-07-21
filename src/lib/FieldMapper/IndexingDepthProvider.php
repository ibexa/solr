<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\FieldMapper;

use Ibexa\Contracts\Core\Persistence\Content\Type as ContentType;

class IndexingDepthProvider
{
    /**
     * @param array<string, int> $contentTypeMap
     */
    public function __construct(
        private array $contentTypeMap = [],
        private readonly int $defaultIndexingDepth = 1
    ) {
    }

    /**
     * Returns max depth of indexing for given content type.
     */
    public function getMaxDepthForContent(ContentType $contentType): int
    {
        return $this->contentTypeMap[$contentType->identifier] ?? $this->defaultIndexingDepth;
    }

    public function getMaxDepth(): int
    {
        if (!empty($this->contentTypeMap)) {
            return max($this->defaultIndexingDepth, ...array_values($this->contentTypeMap));
        }

        return $this->defaultIndexingDepth;
    }
}
