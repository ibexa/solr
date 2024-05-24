<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Search;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for Solr related tests.
 */
abstract class TestCase extends BaseTestCase
{
}

class_alias(TestCase::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\TestCase');
