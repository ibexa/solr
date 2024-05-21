<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Gateway;

use Ibexa\Solr\Gateway;
use Ibexa\Solr\Gateway\GatewayRegistry;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class GatewayRegistryTest extends TestCase
{
    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::addGateway
     */
    public function testAddGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        self::assertCount(1, $registry->getGateways());
    }

    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::getGateway
     */
    public function testGetGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        self::assertInstanceOf(Gateway::class, $registry->getGateway('connection1'));
    }

    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::getGateway
     */
    public function testGetGatewayForMissingConnection(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $registry = new GatewayRegistry();
        $registry->getGateway('connection1');
    }

    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::hasGateway
     */
    public function testHasGateway(): void
    {
        $registry = new GatewayRegistry();
        $registry->addGateway('connection1', $this->getGatewayMock());

        self::assertTrue($registry->hasGateway('connection1'));
    }

    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::setGateways
     */
    public function testSetGateways(): void
    {
        $gateways = ['connection1' => $this->getGatewayMock()];

        $registry = new GatewayRegistry();
        $registry->setGateways($gateways);

        self::assertEquals($gateways, $registry->getGateways());
    }

    /**
     * @covers \Ibexa\Solr\Gateway\GatewayRegistry::getGateways
     */
    public function testGetGateways(): void
    {
        $registry = new GatewayRegistry(['connection1' => $this->getGatewayMock()]);

        self::assertCount(1, $registry->getGateways());
    }

    /**
     * @return \Ibexa\Solr\Gateway|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getGatewayMock(): Gateway
    {
        return $this->createMock(Gateway::class);
    }
}
