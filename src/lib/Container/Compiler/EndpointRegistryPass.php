<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Container\Compiler;

use Ibexa\Solr\Gateway\EndpointRegistry;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Endpoints.
 */
class EndpointRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(
                EndpointRegistry::class
            )
        ) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(
            EndpointRegistry::class
        );

        $endpoints = $container->findTaggedServiceIds('ibexa.search.solr.endpoint');

        foreach ($endpoints as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        "'ibexa.search.solr.endpoint' service tag needs an 'alias' attribute " .
                        'to identify the endpoint.'
                    );
                }

                $fieldRegistryDefinition->addMethodCall(
                    'registerEndpoint',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}

class_alias(EndpointRegistryPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\EndpointRegistryPass');
