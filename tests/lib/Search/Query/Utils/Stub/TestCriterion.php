<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Utils\Stub;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;

final class TestCriterion extends Criterion
{
    public function __construct() {}

    public function getSpecifications(): array
    {
        return [];
    }
}
