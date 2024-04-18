<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Solr\Query\CriterionVisitor;

abstract class CommonCriterionVisitor extends CriterionVisitor
{
    abstract protected function getTargetField(): string;
}
