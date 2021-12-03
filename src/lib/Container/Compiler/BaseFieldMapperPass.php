<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Base compiler pass for aggregate document field mappers.
 */
abstract class BaseFieldMapperPass implements CompilerPassInterface
{
    /**
     * Service ID of the aggregate plugin.
     */
    public const AGGREGATE_MAPPER_SERVICE_ID = null;

    /**
     * Service tag of plugins registering to the aggregate one.
     */
    public const AGGREGATE_MAPPER_SERVICE_TAG = null;

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::AGGREGATE_MAPPER_SERVICE_ID)) {
            return;
        }

        $aggregateMapperDefinition = $container->getDefinition(static::AGGREGATE_MAPPER_SERVICE_ID);
        $taggedMapperServiceIds = $container->findTaggedServiceIds(static::AGGREGATE_MAPPER_SERVICE_TAG);

        foreach ($taggedMapperServiceIds as $id => $attributes) {
            $aggregateMapperDefinition->addMethodCall(
                'addMapper',
                [
                    new Reference($id),
                ]
            );
        }
    }
}

class_alias(BaseFieldMapperPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\BaseFieldMapperPass');
