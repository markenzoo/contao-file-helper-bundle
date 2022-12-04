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

use Contao\Backend;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;

$GLOBALS['TL_DCA']['tl_files']['list']['operations']['usage'] = [
    'href' => 'act=usage',
    'icon' => 'bundles/contaofilehelper/icons/file-usage.svg',
    'button_callback' => ['dca_tl_files', 'showUsage'],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Felix Kästner <kaestner@markenzoo.de>
 */
class dca_tl_files extends Backend
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
     */
    public function showUsage($row, $href, $label, $title, $icon, $attributes): string
    {
        if (Input::get('usage')) {
            return '';
        }

        if ('folder' !== $row['type']) {
            return '<a href="contao/usage?src='.base64_encode($row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.' onclick="Backend.openModalIframe({\'title\':\''.str_replace("'", "\\'", StringUtil::specialchars($row['fileNameEncoded'])).'\',\'url\':this.href});return false">'.Image::getHtml($icon, $label).'</a> ';
        }

        return '';
    }
}
