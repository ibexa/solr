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
     * @param array<string, list<string>|string> $headers
     */
    public function __construct(
        public array $headers = [],
        public string $body = ''
    ) {
    }
}
