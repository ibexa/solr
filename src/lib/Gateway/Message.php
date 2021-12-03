<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway;

/**
 * Simple response struct.
 */
class Message
{
    /**
     * Response headers.
     *
     * @var array
     */
    public $headers;

    /**
     * Response body.
     *
     * @var string
     */
    public $body;

    /**
     * Construct from headers and body.
     *
     * @param string $body
     */
    public function __construct(array $headers = [], $body = '')
    {
        $this->headers = $headers;
        $this->body = $body;
    }
}

class_alias(Message::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\Message');
