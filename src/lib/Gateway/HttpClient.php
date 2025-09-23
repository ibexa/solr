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
    public function request(string $method, Endpoint $endpoint, string $path, ?Message $message = null): Message;
}

class_alias(HttpClient::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient');
