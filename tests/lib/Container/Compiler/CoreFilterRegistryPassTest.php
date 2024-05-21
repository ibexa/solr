<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Solr\Container\Compiler;

use Ibexa\Solr\Container\Compiler\CoreFilterRegistryPass;
use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CoreFilterRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(CoreFilterRegistry::class, new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CoreFilterRegistryPass());
    }

    public function testAddCoreFilter(): void
    {
        $definition = new Definition();
        $definition->addTag(CoreFilterRegistryPass::CORE_FILTER_SERVICE_TAG, ['connection' => 'connection1']);
        $this->setDefinition('service_1', $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            CoreFilterRegistry::class,
            'addCoreFilter',
            ['connection1', new Reference('service_1')]
        );
    }
}
