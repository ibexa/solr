<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Container\Compiler;

use Ibexa\Solr\Container\Compiler\AggregateFacetBuilderVisitorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AggregateFacetBuilderVisitorPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDefinition(
            'ibexa.solr.query.content.facet_builder_visitor.aggregate',
            new Definition()
        );
        $this->setDefinition(
            'ibexa.solr.query.location.facet_builder_visitor.aggregate',
            new Definition()
        );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AggregateFacetBuilderVisitorPass());
    }

    public function testAddVisitor()
    {
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ibexa.search.solr.query.content.facet_builder.visitor');
        $this->setDefinition($serviceId, $def);

        $serviceId2 = 'service_id2';
        $def = new Definition();
        $def->addTag('ibexa.search.solr.query.location.facet_builder.visitor');
        $this->setDefinition($serviceId2, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ibexa.solr.query.content.facet_builder_visitor.aggregate',
            'addVisitor',
            [new Reference($serviceId)]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ibexa.solr.query.location.facet_builder_visitor.aggregate',
            'addVisitor',
            [new Reference($serviceId2)]
        );
    }
}

class_alias(AggregateFacetBuilderVisitorPassTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Container\Compiler\AggregateFacetBuilderVisitorPassTest');
