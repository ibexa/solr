<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Solr;

use Ibexa\Bundle\Solr\IbexaSolrBundle;
use Ibexa\Contracts\Core\Search\Handler;
use Ibexa\Contracts\Core\Test\IbexaTestKernel as BaseIbexaTestKernel;
use Ibexa\Solr\Handler as SolrHandler;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IbexaTestKernel extends BaseIbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new IbexaSolrBundle();
    }

    protected static function getExposedServicesById(): iterable
    {
        yield from parent::getExposedServicesById();

        yield SolrHandler::class => Handler::class;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(static function (ContainerBuilder $container): void {
            (new SolrTestContainerBuilder())->loadSolrSettings($container);
        });
    }
}
