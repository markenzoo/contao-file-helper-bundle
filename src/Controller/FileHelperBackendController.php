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

namespace Markenzoo\ContaoFileHelperBundle\Controller;

use Markenzoo\ContaoFileHelperBundle\Module\BackendFileUsage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;  // Contao>=4.9 provides its own AbstractController class under Contao\CoreBundle\Controller\AbstractController, we use the Symfony one for backwards compatibility
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao", defaults={
 *     "_scope" = "backend",
 *     "_token_check" = true,
 * })
 */
class FileHelperBackendController extends AbstractController
{
    /**
     * @Route("/usage", name="file_helper_file_usage")
     */
    public function usageAction(): Response
    {
        $this->get('contao.framework')->initialize(); // The new AbstractController from Contao>=4.9 provides this via $this->initializeContaoFramework();

        $controller = new BackendFileUsage();

        return $controller->run();
    }
}
