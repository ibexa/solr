<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Exception;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the DateMetadata criterion.
 */
abstract class DateMetadata extends CriterionVisitor
{
    /**
     * Map value to a proper Solr date representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getSolrTime($value)
    {
        if (is_numeric($value)) {
            $date = new \DateTime("@{$value}");
        } else {
            try {
                $date = new \DateTime($value);
            } catch (Exception $e) {
                throw new \InvalidArgumentException('Invalid date provided: ' . $value);
            }
        }

        return $date->format('Y-m-d\\TH:i:s\\Z');
    }
}

class_alias(DateMetadata::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\DateMetadata');
