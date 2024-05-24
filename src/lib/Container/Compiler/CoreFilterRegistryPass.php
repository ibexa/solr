<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Container\Compiler;

use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CoreFilterRegistryPass implements CompilerPassInterface
{
    public const CORE_FILTER_SERVICE_TAG = 'ibexa.search.solr.core.filter';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(CoreFilterRegistry::class)) {
            return;
        }

        $coreFilterRegistryDefinition = $container->getDefinition(CoreFilterRegistry::class);

        $coreFilters = $container->findTaggedServiceIds(self::CORE_FILTER_SERVICE_TAG);

        foreach ($coreFilters as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['connection'])) {
                    throw new LogicException(
                        "'ibexa.search.solr.core.filter' service tag needs a 'connection' attribute " .
                        'to identify the Gateway.'
                    );
                }

                $coreFilterRegistryDefinition->addMethodCall(
                    'addCoreFilter',
                    [
                        $attribute['connection'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}

class_alias(CoreFilterRegistryPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\CoreFilterRegistryPass');
