<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway\EndpointResolver;

use Ibexa\Solr\Gateway\Endpoint;
use Ibexa\Solr\Gateway\EndpointResolver;
use Ibexa\Solr\Gateway\SingleEndpointResolver;
use RuntimeException;

/**
 * NativeEndpointResolver provides Solr endpoints for a Content translations.
 */
class NativeEndpointResolver implements EndpointResolver, SingleEndpointResolver
{
    /**
     * Result of hasMultipleEndpoints() once called the first time.
     */
    protected ?bool $hasMultiple = null;

    /**
     * Create from Endpoint names.
     *
     * @param list<string> $entryEndpoints
     * @param array<string, string> $endpointMap
     */
    public function __construct(
        /**
         * Holds an array of Solr entry endpoint names.
         */
        private array $entryEndpoints = [],
        /**
         * Holds a map of translations to Endpoint names, with language code as key
         * and Endpoint name as value.
         *
         * <code>
         *  array(
         *      "cro-HR" => "endpoint1",
         *      "eng-GB" => "endpoint2",
         *  );
         * </code>
         */
        private readonly array $endpointMap = [],
        /**
         * Holds a name of the default Endpoint used for translations, if configured.
         */
        private readonly ?string $defaultEndpoint = null,
        /**
         * Holds a name of the Endpoint used to index translations in main languages, if configured.
         */
        private readonly ?string $mainLanguagesEndpoint = null
    ) {
    }

    public function getEntryEndpoint(): string
    {
        if (empty($this->entryEndpoints)) {
            throw new RuntimeException('No entry endpoints defined');
        }

        return reset($this->entryEndpoints);
    }

    public function getIndexingTarget(string $languageCode): string
    {
        if (isset($this->endpointMap[$languageCode])) {
            return $this->endpointMap[$languageCode];
        }

        if (isset($this->defaultEndpoint)) {
            return $this->defaultEndpoint;
        }

        throw new RuntimeException("Language '{$languageCode}' is not mapped to Solr endpoint");
    }

    public function getMainLanguagesEndpoint(): ?string
    {
        return $this->mainLanguagesEndpoint;
    }

    public function getSearchTargets(array $languageSettings): array
    {
        $languages = (
            empty($languageSettings['languages']) ?
                [] :
                $languageSettings['languages']
        );
        $useAlwaysAvailable = (
            !isset($languageSettings['useAlwaysAvailable']) ||
            $languageSettings['useAlwaysAvailable'] === true
        );

        if (($useAlwaysAvailable || empty($languages)) && !isset($this->mainLanguagesEndpoint)) {
            return $this->getEndpoints();
        }

        $targetSet = [];

        foreach ($languages as $languageCode) {
            if (isset($this->endpointMap[$languageCode])) {
                $targetSet[$this->endpointMap[$languageCode]] = true;
            } elseif (isset($this->defaultEndpoint)) {
                $targetSet[$this->defaultEndpoint] = true;
            } else {
                throw new RuntimeException("Language '{$languageCode}' is not mapped to Solr endpoint");
            }
        }

        if (($useAlwaysAvailable || empty($targetSet)) && isset($this->mainLanguagesEndpoint)) {
            $targetSet[$this->mainLanguagesEndpoint] = true;
        }

        if (empty($targetSet)) {
            throw new RuntimeException('No endpoints defined for given language settings');
        }

        return array_keys($targetSet);
    }

    public function getEndpoints(): array
    {
        $endpointSet = array_flip($this->endpointMap);

        if (isset($this->defaultEndpoint)) {
            $endpointSet[$this->defaultEndpoint] = true;
        }

        if (isset($this->mainLanguagesEndpoint)) {
            $endpointSet[$this->mainLanguagesEndpoint] = true;
        }

        if (empty($endpointSet)) {
            throw new RuntimeException('No endpoints defined');
        }

        return array_keys($endpointSet);
    }

    public function hasMultipleEndpoints(): bool
    {
        if ($this->hasMultiple !== null) {
            return $this->hasMultiple;
        }

        $endpointSet = array_flip($this->endpointMap);

        if (isset($this->defaultEndpoint)) {
            $endpointSet[$this->defaultEndpoint] = true;
        }

        if (isset($this->mainLanguagesEndpoint)) {
            $endpointSet[$this->mainLanguagesEndpoint] = true;
        }

        return $this->hasMultiple = \count($endpointSet) > 1;
    }
}
