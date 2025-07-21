<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Container\Compiler;

use Ibexa\Solr\Gateway\GatewayRegistry;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class GatewayRegistryPass implements CompilerPassInterface
{
    public const string GATEWAY_SERVICE_TAG = 'ibexa.search.solr.gateway';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(GatewayRegistry::class)) {
            return;
        }

        $gatewayRegistryDefinition = $container->getDefinition(GatewayRegistry::class);

        $gateways = $container->findTaggedServiceIds(self::GATEWAY_SERVICE_TAG);

        foreach ($gateways as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['connection'])) {
                    throw new LogicException(
                        "'ibexa.search.solr.gateway' service tag needs a 'connection' attribute " .
                        'to identify the Gateway.'
                    );
                }

                $gatewayRegistryDefinition->addMethodCall(
                    'addGateway',
                    [
                        $attribute['connection'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
