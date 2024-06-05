<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway;

/**
 * Additional interface for Endpoint resolvers which resolves Solr backend endpoints.
 */
interface SingleEndpointResolver
{
    /**
     * Returns true if current configurations has several endpoints.
     *
     * @return bool
     */
    public function hasMultipleEndpoints();
}
