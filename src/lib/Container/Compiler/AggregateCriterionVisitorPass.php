<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Storage criterion visitors.
 */
class AggregateCriterionVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition('ibexa.solr.query.content.criterion_visitor.aggregate') &&
            !$container->hasDefinition('ibexa.solr.query.location.criterion_visitor.aggregate')
        ) {
            return;
        }

        if ($container->hasDefinition('ibexa.solr.query.content.criterion_visitor.aggregate')) {
            $aggregateContentCriterionVisitorDefinition = $container->getDefinition(
                'ibexa.solr.query.content.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ibexa.search.solr.query.content.criterion.visitor'
            );

            $this->addHandlers($aggregateContentCriterionVisitorDefinition, $visitors);
        }

        if ($container->hasDefinition('ibexa.solr.query.location.criterion_visitor.aggregate')) {
            $aggregateLocationCriterionVisitorDefinition = $container->getDefinition(
                'ibexa.solr.query.location.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ibexa.search.solr.query.location.criterion.visitor'
            );

            $this->addHandlers($aggregateLocationCriterionVisitorDefinition, $visitors);
        }
    }

    protected function addHandlers(Definition $definition, $handlers)
    {
        foreach ($handlers as $id => $attributes) {
            $definition->addMethodCall('addVisitor', [new Reference($id)]);
        }
    }
}

class_alias(AggregateCriterionVisitorPass::class, 'EzSystems\EzPlatformSolrSearchEngine\Container\Compiler\AggregateCriterionVisitorPass');
