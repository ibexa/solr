<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr;

use Ibexa\Bundle\Solr\DependencyInjection\IbexaSolrExtension;
use Ibexa\Solr\Container\Compiler\AggregateCriterionVisitorPass;
use Ibexa\Solr\Container\Compiler\AggregateFacetBuilderVisitorPass;
use Ibexa\Solr\Container\Compiler\AggregateSortClauseVisitorPass;
use Ibexa\Solr\Container\Compiler\CoreFilterRegistryPass;
use Ibexa\Solr\Container\Compiler\EndpointRegistryPass;
use Ibexa\Solr\Container\Compiler\FieldMapperPass;
use Ibexa\Solr\Container\Compiler\GatewayRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaSolrBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FieldMapperPass\BlockFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\BlockTranslationFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\ContentFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\ContentTranslationFieldMapperPass());
        $container->addCompilerPass(new FieldMapperPass\LocationFieldMapperPass());
        $container->addCompilerPass(new AggregateCriterionVisitorPass());
        $container->addCompilerPass(new AggregateFacetBuilderVisitorPass());
        $container->addCompilerPass(new AggregateSortClauseVisitorPass());
        $container->addCompilerPass(new EndpointRegistryPass());
        $container->addCompilerPass(new GatewayRegistryPass());
        $container->addCompilerPass(new CoreFilterRegistryPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (!isset($this->extension)) {
            $this->extension = new IbexaSolrExtension();
        }

        return $this->extension;
    }
}
