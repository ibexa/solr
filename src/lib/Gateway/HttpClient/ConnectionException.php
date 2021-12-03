<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway\HttpClient;

use RuntimeException;

/**
 * HTTPClient connection exception.
 */
class ConnectionException extends RuntimeException
{
    public function __construct($server, $path, $method)
    {
        parent::__construct(
            "Could not connect to server $server."
        );
    }
}

class_alias(ConnectionException::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\HttpClient\ConnectionException');
