<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\DataCollector;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\Message;

final class SolrRequest
{
    public string $method;

    public Endpoint $endpoint;

    public ?string $path;

    public ?Message $message;

    public ?Message $response;

    public function __construct(
        string $method,
        Endpoint $endpoint,
        ?string $path,
        ?Message $message,
        ?Message $response
    ) {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->path = $path;
        $this->message = $message;
        $this->response = $response;
    }

    public function getRequestAsString(): string
    {
        $text = $this->method . ' ' . $this->endpoint->getURL() . $this->endpoint->path . PHP_EOL;
        if ($this->message !== null) {
            foreach ($this->message->headers as $name => $value) {
                $text .= $name . ': ' . $value . PHP_EOL;
            }

            if (!empty($this->message->body)) {
                $text .= PHP_EOL;
                $text .= $this->message->body;
            }
        }

        return $text;
    }

    public function getResponseAsString(): string
    {
        if ($this->response === null) {
            return '';
        }

        $text = '';
        foreach ($this->response->headers as $name => $value) {
            $text .= $name . ': ' . $value . PHP_EOL;
        }

        if (!empty($this->response->body)) {
            $text .= PHP_EOL;
            $text .= $this->response->body;
        }

        return $text;
    }
}
