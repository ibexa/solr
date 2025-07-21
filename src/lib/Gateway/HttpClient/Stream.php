<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway\HttpClient;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\HttpClient;
use Ibexa\Solr\Gateway\Message;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Simple PHP stream based HTTP client.
 *
 * @internal type-hint {@see \Ibexa\Solr\Gateway\HttpClient} instead.
 */
class Stream implements HttpClient, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param int $timeout Timeout for connection in seconds.
     */
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly int $timeout = 10
    ) {
        $this->setLogger(new NullLogger());
    }

    public function request(string $method, Endpoint $endpoint, string $path, ?Message $message = null): Message
    {
        $message ??= new Message();

        try {
            return $this->getResponseMessage(
                $method,
                $endpoint,
                $path,
                $message
            );
        } catch (ExceptionInterface $e) {
            throw new ConnectionException($endpoint->getURL(), $path, $method, $e);
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getResponseMessage(
        string $method,
        Endpoint $endpoint,
        string $path,
        Message $message
    ): Message {
        if ($endpoint->user !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode("{$endpoint->user}:{$endpoint->pass}");
        }

        $response = $this->client->request(
            $method,
            $endpoint->getURL() . $path,
            [
                'headers' => $message->headers,
                'timeout' => $this->timeout,
                'body' => $message->body,
            ]
        );

        $headers = array_merge(
            [
                // hardcoded for BC, not provided by symfony/http-client, nor needed
                'version' => '1.1',
                'status' => $response->getStatusCode(),
            ],
            $response->getHeaders()
        );

        return new Message(
            $headers,
            $response->getContent(),
        );
    }
}
