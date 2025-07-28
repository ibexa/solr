<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway;

use Ibexa\Contracts\Core\Persistence\ValueObject;

/**
 * @property-read string $scheme
 * @property-read string|null $user
 * @property-read string|null $pass
 * @property-read string $host
 * @property-read int $port
 * @property-read string $path
 * @property-read string $core
 */
class Endpoint extends ValueObject
{
    /**
     * Holds scheme, 'http' or 'https'.
     */
    protected string $scheme;

    /**
     * Holds basic HTTP authentication username.
     */
    protected ?string $user = null;

    /**
     * Holds basic HTTP authentication password.
     */
    protected ?string $pass = null;

    /**
     * Holds hostname.
     */
    protected string $host;

    /**
     * Holds port number.
     */
    protected string $port;

    /**
     * Holds path.
     */
    protected string $path;

    /**
     * Holds core name.
     */
    protected string $core;

    /**
     * Parse DSN settings if present, otherwise take parameters as is.
     *
     * @param array<string, mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        // If dns is defined parse it to individual parts
        if (!empty($properties['dsn'])) {
            $properties = parse_url((string) $properties['dsn']) + $properties;
            unset($properties['dsn']);

            // if dns contained fragment we set that on core config, query however will result in exception.
            if (isset($properties['fragment'])) {
                $properties['core'] = $properties['fragment'];
                unset($properties['fragment']);
            }
        }

        parent::__construct($properties);
    }

    /**
     * Returns Endpoint's identifier, to be used for targeting specific logical indexes.
     */
    public function getIdentifier(): string
    {
        return "{$this->host}:{$this->port}{$this->path}/{$this->core}";
    }

    /**
     * Returns full HTTP URL of the Endpoint.
     */
    public function getURL(): string
    {
        $authorization = (!empty($this->user) ? "{$this->user}:{$this->pass}@" : '');

        return "{$this->scheme}://" . $authorization . $this->getIdentifier();
    }
}
