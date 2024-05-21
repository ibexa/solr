<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway;

/**
 * Interface for Http Client implementations.
 */
interface HttpClient
{
    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param \Ibexa\Solr\Gateway\Endpoint $endpoint
     * @param string $path
     * @param \Ibexa\Solr\Gateway\Message $message
     *
     * @return \Ibexa\Solr\Gateway\Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null);
}
