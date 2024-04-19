<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Solr\Query\Common\CriterionVisitor\BaseIsContainer;

/**
 * @internal
 */
final class IsContainer extends BaseIsContainer
{
    public function getTargetField(): string
    {
        return 'content_type_is_container_b';
    }
}
