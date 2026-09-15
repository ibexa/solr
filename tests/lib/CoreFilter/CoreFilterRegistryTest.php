<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\CoreFilter;

use Ibexa\Solr\CoreFilter;
use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\CoreFilter\CoreFilterRegistry::class, 'addCoreFilter')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\CoreFilter\CoreFilterRegistry::class, 'getCoreFilter')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\Gateway\GatewayRegistry::class, 'getGateway')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\CoreFilter\CoreFilterRegistry::class, 'hasCoreFilter')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\CoreFilter\CoreFilterRegistry::class, 'setCoreFilters')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Ibexa\Solr\CoreFilter\CoreFilterRegistry::class, 'getCoreFilters')]
class CoreFilterRegistryTest extends TestCase
{
    public function testAddCoreFilter(): void
    {
        $registry = new CoreFilterRegistry();
        $registry->addCoreFilter('connection1', $this->getCoreFilterMock());

        self::assertCount(1, $registry->getCoreFilters());
    }

    public function testGetCoreFilter(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        self::assertInstanceOf(CoreFilter::class, $registry->getCoreFilter('connection1'));
    }

    public function testGetCoreFilterForMissingConnection(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $registry = new CoreFilterRegistry();
        $registry->getCoreFilter('connection1');
    }

    public function testHasCoreFilter(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        self::assertTrue($registry->hasCoreFilter('connection1'));
    }

    public function testSetCoreFilters(): void
    {
        $coreFilters = ['connection1' => $this->getCoreFilterMock()];

        $registry = new CoreFilterRegistry();
        $registry->setCoreFilters($coreFilters);

        self::assertEquals($coreFilters, $registry->getCoreFilters());
    }

    public function testGetCoreFilters(): void
    {
        $registry = new CoreFilterRegistry(['connection1' => $this->getCoreFilterMock()]);

        self::assertCount(1, $registry->getCoreFilters());
    }

    private function getCoreFilterMock(): CoreFilter&MockObject
    {
        return $this->createMock(CoreFilter::class);
    }
}
