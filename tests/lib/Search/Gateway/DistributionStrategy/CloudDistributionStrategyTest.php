<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Gateway\DistributionStrategy;

use Ibexa\Solr\Gateway\DistributionStrategy\CloudDistributionStrategy;
use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\EndpointRegistry;
use Ibexa\Solr\Gateway\EndpointResolver;
use PHPUnit\Framework\TestCase;

class CloudDistributionStrategyTest extends TestCase
{
    /** @var \Ibexa\Solr\Gateway\DistributionStrategy\CloudDistributionStrategy */
    private $distributionStrategy;

    /** @var \Ibexa\Solr\Gateway\EndpointResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointResolver;

    /** @var \Ibexa\Solr\Gateway\EndpointRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $endpointRegistry;

    protected function setUp(): void
    {
        $this->endpointResolver = $this->createMock(EndpointResolver::class);

        $this->endpointRegistry = $this->createMock(EndpointRegistry::class);
        $this->endpointRegistry
            ->method('getEndpoint')
            ->willReturnCallback(static function ($name) {
                return new Endpoint([
                    'core' => 'collection_' . $name,
                ]);
            });

        $this->distributionStrategy = new CloudDistributionStrategy(
            $this->endpointRegistry,
            $this->endpointResolver
        );
    }

    public function testGetSearchParameters(): void
    {
        $this->endpointResolver
            ->expects($this->once())
            ->method('getEndpoints')
            ->willReturn(['en', 'de', 'fr', 'pl']);

        $parameters = [
            'wt' => 'json',
            'indent' => true,
        ];

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'collection' => 'collection_en,collection_de,collection_fr,collection_pl',
        ], $this->distributionStrategy->getSearchParameters($parameters));
    }

    public function testGetSearchParametersWithLanguageSettings(): void
    {
        $languagesSettings = [
            'languages' => ['eng-GB', 'pol-PL'],
        ];

        $this->endpointResolver
            ->expects($this->once())
            ->method('getSearchTargets')
            ->with($languagesSettings)
            ->willReturn(['en', 'pl']);

        $parameters = [
            'wt' => 'json',
            'indent' => true,
        ];

        $this->assertEquals([
            'wt' => 'json',
            'indent' => true,
            'collection' => 'collection_en,collection_pl',
        ], $this->distributionStrategy->getSearchParameters($parameters, $languagesSettings));
    }
}

class_alias(CloudDistributionStrategyTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Gateway\DistributionStrategy\CloudDistributionStrategyTest');
