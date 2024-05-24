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
 * This compiler pass will register Solr Storage facet builder visitors.
 */
class AggregateFacetBuilderVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processVisitors($container, 'content');
        $this->processVisitors($container, 'location');
    }

    private function processVisitors(ContainerBuilder $container, $name = 'content')
    {
        if (!$container->hasDefinition("ibexa.solr.query.$name.facet_builder_visitor.aggregate")) {
            return;
        }

        $aggregateFacetBuilderVisitorDefinition = $container->getDefinition(
            "ibexa.solr.query.$name.facet_builder_visitor.aggregate"
        );

        foreach ($container->findTaggedServiceIds("ibexa.search.solr.query.$name.facet_builder.visitor") as $id => $attributes) {
            $aggregateFacetBuilderVisitorDefinition->addMethodCall(
                'addVisitor',
                [
                    new Reference($id),
                ]
            );
        }
    }
}

class_alias(AggregateFacetBuilderVisitorPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateFacetBuilderVisitorPass');
