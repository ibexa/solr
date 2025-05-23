<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Search\Gateway;

use Ibexa\Contracts\Core\Repository\Exceptions\PropertyNotFoundException;
use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Tests\Solr\Search\TestCase;

/**
 * Test case for native endpoint resolver.
 */
class EndpointTest extends TestCase
{
    public function testEndpointDsnParsingWithAll(): void
    {
        $actual = new Endpoint(['dsn' => 'https://jura:pura@10.10.10.10:5434/jolr', 'core' => 'core0']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => 'jura',
                'pass' => 'pura',
                'path' => '/jolr',
                'core' => 'core0',
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithoutUser(): void
    {
        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr', 'core' => 'core0']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => null,
                'pass' => null,
                'path' => '/jolr',
                'core' => 'core0',
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithFragment(): void
    {
        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr#core1']);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => null,
                'pass' => null,
                'path' => '/jolr',
                'core' => 'core1',
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingOverridesAllIfSet(): void
    {
        $actual = new Endpoint([
            'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr#core1',
            'scheme' => 'http',
            'host' => '127.1.1.1',
            'port' => 8983,
            'user' => 'ben',
            'pass' => 'pass',
            'path' => '/solr',
            'core' => 'core0',
        ]);
        $expected = new Endpoint([
                'scheme' => 'https',
                'host' => '10.10.10.10',
                'port' => 5434,
                'user' => 'jura',
                'pass' => 'pura',
                'path' => '/jolr',
                'core' => 'core1',
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testEndpointDsnParsingWithQuery(): void
    {
        $this->expectException(PropertyNotFoundException::class);

        $actual = new Endpoint(['dsn' => 'https://10.10.10.10:5434/jolr?query']);
    }
}
