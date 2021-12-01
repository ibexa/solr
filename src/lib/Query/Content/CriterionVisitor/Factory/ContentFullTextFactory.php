<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Content\CriterionVisitor\Factory;

use Ibexa\Solr\Query\Common\CriterionVisitor\Factory\FullTextFactoryAbstract;
use Ibexa\Solr\Query\Content\CriterionVisitor\FullText;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Factory for FullText Criterion Visitor.
 *
 * @see \Ibexa\Solr\Query\Content\CriterionVisitor\FullText
 *
 * @internal
 */
final class ContentFullTextFactory extends FullTextFactoryAbstract
{
    /**
     * Create FullText Criterion Visitor.
     *
     * @return \Ibexa\Contracts\Solr\Query\CriterionVisitor|\Ibexa\Solr\Query\Content\CriterionVisitor\FullText
     */
    public function createCriterionVisitor(): CriterionVisitor
    {
        return new FullText(
            $this->fieldNameResolver,
            $this->tokenizer,
            $this->parser,
            $this->generator,
            $this->indexingDepthProvider->getMaxDepth()
        );
    }
}

class_alias(ContentFullTextFactory::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Content\CriterionVisitor\Factory\ContentFullTextFactory');
