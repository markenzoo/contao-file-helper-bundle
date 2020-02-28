<?php

declare(strict_types=1);

/*
 * This file is part of markenzoo/contao-file-helper-bundle.
 *
 * Copyright (c) 2020 markenzoo eG
 *
 * @package   markenzoo/contao-file-helper-bundle
 * @author    Felix Kästner <kaestner@markenzoo.de>
 * @copyright 2020 markenzoo eG
 * @license   https://github.com/markenzoo/contao-file-helper-bundle/blob/master/LICENSE MIT License
 */

namespace Markenzoo\ContaoFileHelperBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Markenzoo\ContaoFileHelperBundle\ContaoFileHelperBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoFileHelperBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        return $resolver
             ->resolve(__DIR__.'/../Resources/config/routing.yml')
             ->load(__DIR__.'/../Resources/config/routing.yml')
         ;
    }
}
