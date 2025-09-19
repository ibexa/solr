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
     * @param string $method
     * @param \Ibexa\Solr\Gateway\Endpoint $endpoint
     * @param string $path
     *
     * @return \Ibexa\Solr\Gateway\Message
     */
    public function request($method, Endpoint $endpoint, $path, ?Message $message = null);
}

class_alias(HttpClient::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient');
