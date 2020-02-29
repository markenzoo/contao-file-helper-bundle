<?php

declare(strict_types=1);

/*
 * This file is part of markenzoo/markenzoo-bundle.
 *
 * Copyright (c) 2020 markenzoo eG
 *
 * @copyright   Copyright (c) 2020 markenzoo eG.
 * @author      Felix Kästner <kaestner@markenzoo.de>
 * @license     proprietary
 */

$GLOBALS['TL_DCA']['tl_files']['list']['operations']['usage'] = [
    'href' => 'act=usage',
    'icon' => 'bundles/contaofilehelper/icons/file-usage.svg',
    'button_callback' => ['contao_file_helper_bundle_dca_tl_files', 'showUsage'],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Felix Kästner <kaestner@markenzoo.de>
 */
class contao_file_helper_bundle_dca_tl_files extends Contao\Backend
{
    /**
     * Return file usage button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return null|string
     */
    public function showUsage($row, $href, $label, $title, $icon, $attributes): ?string
    {
        if (Contao\Input::get('usage')) {
            return '';
        }

        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        if ('folder' !== $row['type'] && file_exists($rootDir.'/'.$row['id']) && !is_dir($rootDir.'/'.$row['id'])) {
            return '<a href="contao/usage?src='.base64_encode($row['id']).'" title="'.Contao\StringUtil::specialchars($title).'"'.$attributes.' onclick="Backend.openModalIframe({\'title\':\''.str_replace("'", "\\'", Contao\StringUtil::specialchars($row['fileNameEncoded'])).'\',\'url\':this.href});return false">'.Contao\Image::getHtml($icon, $label).'</a> ';
        }
    }
}
