<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Solr\CoreFilter;

use Ibexa\Solr\CoreFilter;
use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class CoreFilterRegistryTest extends TestCase
{
    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry::addCoreFilter
     */
    public function testAddCoreFilter(): void
    {
        $registry = new CoreFilterRegistry();
        $registry->addCoreFilter('connection1', $this->getCoreFilterMock());

        $this->assertCount(1, $registry->getCoreFilters());
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry::getCoreFilter
     */
    public function testGetCoreFilter(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        $this->assertInstanceOf(CoreFilter::class, $registry->getCoreFilter('connection1'));
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\Gateway\GatewayRegistry::getGateway
     */
    public function testGetCoreFilterForMissingConnection(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $registry = new CoreFilterRegistry();
        $registry->getCoreFilter('connection1');
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry::hasCoreFilter
     */
    public function testHasCoreFilter(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        $this->assertTrue($registry->hasCoreFilter('connection1'));
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry::setCoreFilters
     */
    public function testSetCoreFilters(): void
    {
        $coreFilters = ['connection1' => $this->getCoreFilterMock()];

        $registry = new CoreFilterRegistry();
        $registry->setCoreFilters($coreFilters);

        $this->assertEquals($coreFilters, $registry->getCoreFilters());
    }

    /**
     * @covers \EzSystems\EzPlatformSolrSearchEngine\CoreFilter\CoreFilterRegistry::getCoreFilters
     */
    public function testGetCoreFilters(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        $this->assertCount(1, $registry->getCoreFilters());
    }

    /**
     * @return \Ibexa\Solr\CoreFilter|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getCoreFilterMock(): CoreFilter
    {
        return $this->createMock(CoreFilter::class);
    }
}

class_alias(CoreFilterRegistryTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\CoreFilter\CoreFilterRegistryTest');
