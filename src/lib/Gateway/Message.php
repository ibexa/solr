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
     * Request/Response headers.
     *
     * @var array
     */
    public $headers;

    /**
     * Request/Response body.
     *
     * @var string
     */
    public $body;

    public function __construct(array $headers = [], string $body = '')
    {
        $this->headers = $headers;
        $this->body = $body;
    }
}

class_alias(Message::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\Message');
