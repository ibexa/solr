<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Persistence\Content\Type\Handler;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Visits the ContentTypeIdentifier criterion.
 */
class ContentTypeIdentifierIn extends CriterionVisitor
{
    /**
     * ContentType handler.
     */
    protected Handler $contentTypeHandler;

    protected LoggerInterface $logger;

    /**
     * Create from content type handler and field registry.
     */
    public function __construct(Handler $contentTypeHandler, LoggerInterface $logger = null)
    {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\ContentTypeIdentifier
            && (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $validIds = [];
        $invalidIdentifiers = [];
        $contentTypeHandler = $this->contentTypeHandler;

        foreach ($criterion->value as $identifier) {
            try {
                $validIds[] = $contentTypeHandler->loadByIdentifier((string) $identifier)->id;
            } catch (NotFoundException $e) {
                // Filter out non-existing content types, but track for code below
                $invalidIdentifiers[] = $identifier;
            }
        }

        if (\count($invalidIdentifiers) > 0) {
            $this->logger->warning(
                sprintf(
                    'Invalid content type identifiers provided for ContentTypeIdentifier criterion: %s',
                    implode(', ', $invalidIdentifiers)
                )
            );
        }

        if (\count($validIds) === 0) {
            return '(NOT *:*)';
        }

        return '(' .
            implode(
                ' OR ',
                array_map(
                    static function (string $value): string {
                        return 'content_type_id_id:"' . $value . '"';
                    },
                    $validIds
                )
            ) .
            ')';
    }
}
