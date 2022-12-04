<?php

declare(strict_types=1);

/*
 * This file is part of markenzoo/contao-file-helper-bundle.
 *
 * Copyright (c) 2022 markenzoo eG
 *
 * @package   markenzoo/contao-file-helper-bundle
 * @author    Felix Kästner <kaestner@markenzoo.de>
 * @author    Mathias Arzberger <https://github.com/MDevster>
 * @copyright 2022 markenzoo eG
 * @license   https://github.com/markenzoo/contao-file-helper-bundle/blob/master/LICENSE MIT License
 */

namespace Markenzoo\ContaoFileHelperBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Markenzoo\ContaoFileHelperBundle\Module\BackendFileUsage;
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
        $this->initializeContaoFramework();

        $controller = new BackendFileUsage();

        return $controller->run();
    }
}
