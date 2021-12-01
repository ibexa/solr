<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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

class_alias(HttpClient::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient');
