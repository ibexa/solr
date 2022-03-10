<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\DataCollector;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\HttpClient;
use Ibexa\Solr\Gateway\Message;

final class TraceableHttpClient implements HttpClient
{
    private HttpClient $innerClient;

    /** @var \Ibexa\Bundle\Solr\DataCollector\SolrRequest[] */
    private array $requests;

    public function __construct(HttpClient $innerClient)
    {
        $this->innerClient = $innerClient;
        $this->requests = [];
    }

    public function request($method, Endpoint $endpoint, $path, Message $message = null): Message
    {
        $response = $this->innerClient->request($method, $endpoint, $path, $message);

        $this->requests[] = new SolrRequest($method, $endpoint, $path, $message, $response);

        return $response;
    }

    /**
     * @return \Ibexa\Bundle\Solr\DataCollector\SolrRequest[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }
}
