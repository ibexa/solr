<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace Ibexa\Bundle\Solr;

use Ibexa\Bundle\Solr\DependencyInjection\IbexaSolrExtension;
use Ibexa\Solr\Container\Compiler\CoreFilterRegistryPass;
use Ibexa\Solr\Container\Compiler\GatewayRegistryPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ibexa\Solr\Container\Compiler\AggregateCriterionVisitorPass;
use Ibexa\Solr\Container\Compiler\AggregateFacetBuilderVisitorPass;
use Ibexa\Solr\Container\Compiler\AggregateSortClauseVisitorPass;
use Ibexa\Solr\Container\Compiler\FieldMapperPass;
use Ibexa\Solr\Container\Compiler\EndpointRegistryPass;
use Ibexa\Core\Base\Container\Compiler\Search\AggregateFieldValueMapperPass;
use Ibexa\Core\Base\Container\Compiler\Search\FieldRegistryPass;

class IbexaSolrBundle extends Bundle
{
    public function build(ContainerBuilder $container)
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

        $container->addCompilerPass(new AggregateFieldValueMapperPass());
        $container->addCompilerPass(new FieldRegistryPass());
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new IbexaSolrExtension();
        }

        return $this->extension;
    }
}

class_alias(IbexaSolrBundle::class, 'EzSystems\EzPlatformSolrSearchEngineBundle\EzSystemsEzPlatformSolrSearchEngineBundle');
