<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class SolrDataCollector extends AbstractDataCollector
{
    private TraceableHttpClient $client;

    public function __construct(TraceableHttpClient $client)
    {
        $this->client = $client;
    }

    public static function getTemplate(): ?string
    {
        return 'data_collector/solr.html.twig';
    }

    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        $this->data['requests'] = $this->client->getRequests();
    }

    public function reset(): void
    {
        $this->data = [
            'requests' => [],
        ];
    }

    /**
     * @return \Ibexa\Bundle\Solr\DataCollector\SolrRequest[]
     */
    public function getRequests(): array
    {
        return $this->data['requests'];
    }

    public function getRequestCount(): int
    {
        return count($this->data['requests']);
    }
}
