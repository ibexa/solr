<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Location\CriterionVisitor;

use Ibexa\Solr\Query\Common\CriterionVisitor\BaseIsContainer;

final class IsContainer extends BaseIsContainer
{
    public function getTargetField(): string
    {
        return 'is_container_b';
    }
}
