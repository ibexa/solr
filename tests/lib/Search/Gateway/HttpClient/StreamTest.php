<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Gateway\HttpClient;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\HttpClient\Stream;
use Ibexa\Solr\Gateway\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StreamTest extends TestCase
{
    private const TIMEOUT = 10;

    /** @var \Symfony\Contracts\HttpClient\HttpClientInterface&\PHPUnit\Framework\MockObject\MockObject */
    private HttpClientInterface $httpClient;

    private Stream $stream;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->stream = new Stream($this->httpClient, self::TIMEOUT);
    }

    /**
     * @dataProvider provideRequestData
     *
     * @param array<string, mixed> $endpointConfig
     * @param array<string, string> $expectedHeaders
     */
    public function testRequest(
        string $httpMethod,
        array $endpointConfig,
        string $path,
        ?Message $message,
        array $expectedHeaders,
        string $expectedUrl,
        string $expectedBody
    ): void {
        $endpoint = new Endpoint($endpointConfig);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with(
                $httpMethod,
                $expectedUrl,
                [
                    'headers' => $expectedHeaders,
                    'timeout' => self::TIMEOUT,
                    'body' => $expectedBody,
                ]
            )
            ->willReturn($this->createSuccessfulResponse());

        $this->stream->request($httpMethod, $endpoint, $path, $message);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, string, ?Message, array<string, string>, string, string}>
     */
    public static function provideRequestData(): iterable
    {
        yield 'GET select without credentials' => [
            'GET',
            [
                'scheme' => 'http',
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr',
                'core' => 'collection1',
            ],
            '/select',
            null,
            [],
            'http://127.0.0.1:8983/solr/collection1/select',
            '',
        ];

        yield 'POST update with credentials adds authorization header' => [
            'POST',
            [
                'scheme' => 'http',
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr',
                'core' => 'collection1',
                'user' => 'admin',
                'pass' => 'secret',
            ],
            '/update',
            null,
            [
                'Authorization' => 'Basic ' . base64_encode('admin:secret'),
            ],
            'http://127.0.0.1:8983/solr/collection1/update',
            '',
        ];

        yield 'POST update with credentials preserves existing message headers' => [
            'POST',
            [
                'scheme' => 'http',
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr',
                'core' => 'collection1',
                'user' => 'admin',
                'pass' => 'secret',
            ],
            '/update',
            new Message(['Content-Type' => 'application/json'], '{}'),
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('admin:secret'),
            ],
            'http://127.0.0.1:8983/solr/collection1/update',
            '{}',
        ];
    }

    public function testRequestWithCredentialsDoesNotEmbedCredentialsInUrl(): void
    {
        $endpoint = new Endpoint([
            'scheme' => 'http',
            'host' => '127.0.0.1',
            'port' => 8983,
            'path' => '/solr',
            'core' => 'collection1',
            'user' => 'admin',
            'pass' => 'secret',
        ]);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                self::logicalNot(self::stringContains('admin:secret@')),
                self::anything()
            )
            ->willReturn($this->createSuccessfulResponse());

        $this->stream->request('GET', $endpoint, '/select');
    }

    /**
     * @return \Symfony\Contracts\HttpClient\ResponseInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createSuccessfulResponse(): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getContent')->willReturn('{}');

        return $response;
    }
}
