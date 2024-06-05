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
 * This compiler pass will register Solr Storage sort clause visitors.
 */
class AggregateSortClauseVisitorPass implements CompilerPassInterface
{
    /**
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition('ibexa.solr.query.content.sort_clause_visitor.aggregate') &&
            !$container->hasDefinition('ibexa.solr.query.location.sort_clause_visitor.aggregate')
        ) {
            return;
        }

        if ($container->hasDefinition('ibexa.solr.query.content.sort_clause_visitor.aggregate')) {
            $aggregateContentSortClauseVisitorDefinition = $container->getDefinition(
                'ibexa.solr.query.content.sort_clause_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ibexa.search.solr.query.content.sort_clause.visitor'
            );

            $this->addHandlers($aggregateContentSortClauseVisitorDefinition, $visitors);
        }

        if ($container->hasDefinition('ibexa.solr.query.location.sort_clause_visitor.aggregate')) {
            $aggregateLocationSortClauseVisitorDefinition = $container->getDefinition(
                'ibexa.solr.query.location.sort_clause_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ibexa.search.solr.query.location.sort_clause.visitor'
            );

            $this->addHandlers($aggregateLocationSortClauseVisitorDefinition, $visitors);
        }
    }

    protected function addHandlers(Definition $definition, $handlers)
    {
        foreach ($handlers as $id => $attributes) {
            $definition->addMethodCall('addVisitor', [new Reference($id)]);
        }
    }
}
