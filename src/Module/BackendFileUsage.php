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

namespace Markenzoo\ContaoFileHelperBundle\Module;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Database\Result;
use Contao\Date;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Idna;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Contao\Model\QueryBuilder;
use Contao\StringUtil;
use Contao\System;

class BackendFileUsage extends Backend
{
    /**
     * Database Tables to skip in search.
     *
     * @var array
     */
    private const FILTER = ['tl_files', 'tl_search', 'tl_search_index', 'tl_undo', 'tl_version'];

    /**
     * File.
     *
     * @var string
     */
    protected $strFile;

    /**
     * Database Instance.
     *
     * @var Database
     */
    private $database;

    /**
     * Initialize the controller.
     *
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import(BackendUser::class, 'User');
        parent::__construct();

        if (!System::getContainer()->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            System::log('No Authentication', __METHOD__, TL_GENERAL);
            throw new AccessDeniedException('Access denied');
        }

        System::loadLanguageFile('default');

        $strFile = Input::get('src', true);
        $strFile = base64_decode($strFile, true);
        $strFile = ltrim(rawurldecode($strFile), '/');
        $this->strFile = $strFile;

        // get a handle to the database
        $this->database = Database::getInstance();

        if (!\is_array($GLOBALS['TL_CSS'])) {
            $GLOBALS['TL_CSS'] = [];
        }

        $GLOBALS['TL_CSS'][] = 'bundles/contaofilehelper/css/backend-file-helper.css';

        System::loadLanguageFile('default');
        System::loadLanguageFile('modules');
        System::loadLanguageFile('tl_files');
    }

    /**
     * Run the controller and parse the template.
     *
     * @return Response
     */
    public function run()
    {
        if ('' === $this->strFile) {
            die('No file given');
        }

        // Make sure there are no attempts to hack the file system
        if (preg_match('@^\.+@', $this->strFile) || preg_match('@\.+/@', $this->strFile) || preg_match('@(://)+@', $this->strFile)) {
            die('Invalid file name');
        }

        // Limit preview to the files directory
        if (!preg_match('@^'.preg_quote(Config::get('uploadPath'), '@').'@i', $this->strFile)) {
            die('Invalid path');
        }

        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        // Check whether the file exists
        if (!file_exists($rootDir.'/'.$this->strFile)) {
            die('File not found');
        }

        // Check whether the file is mounted (thanks to Marko Cupic)
        if (!$this->User->hasAccess($this->strFile, 'filemounts')) {
            die('Permission denied');
        }

        // Open the download dialogue
        if (Input::get('download')) {
            $objFile = new File($this->strFile);
            $objFile->sendToBrowser();
        }

        /** @var BackendTemplate $objTemplate */
        $objTemplate = new BackendTemplate('be_file_usage');

        /** @var FilesModel $objModel */
        $objModel = FilesModel::findByPath($this->strFile);

        // Add the resource (see #6880)
        if (null === $objModel && Dbafs::shouldBeSynchronized($this->strFile)) {
            $objModel = Dbafs::addResource($this->strFile);
        }

        if (null !== $objModel) {
            $objTemplate->uuid = StringUtil::binToUuid($objModel->uuid); // see #5211
        }

        // Add the file info
        /** @var File $objFile */
        $objFile = new File($this->strFile);

        // Image
        if ($objFile->isImage) {
            $objTemplate->isImage = true;
            $objTemplate->width = $objFile->viewWidth;
            $objTemplate->height = $objFile->viewHeight;
            $objTemplate->src = $this->urlEncode($this->strFile);
            $objTemplate->dataUri = $objFile->dataUri;
        }

        // Meta data
        if (($objModel = FilesModel::findByPath($this->strFile)) instanceof FilesModel) {
            $arrMeta = StringUtil::deserialize($objModel->meta);

            if (\is_array($arrMeta)) {
                System::loadLanguageFile('languages');

                $objTemplate->meta = $arrMeta;
                $objTemplate->languages = (object) $GLOBALS['TL_LANG']['LNG'];
            }
        }

        $objTemplate->href = ampersand(Environment::get('request')).'&amp;download=1';
        $objTemplate->filesize = $this->getReadableSize($objFile->filesize).' ('.number_format($objFile->filesize, 0, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' Byte)';
        $arrUsages = [];

        // get all tables from the database, we can't rely on Contao Models since that won't include usage in extensions
        $arrTables = $this->database->listTables();
        // remove tables we don't want to search in
        $arrTables = array_filter($arrTables, function ($table) {
            return !\in_array($table, self::FILTER, true);
        });

        foreach ($arrTables as $strTable) {
            $arrFields = $this->database->listFields($strTable);

            foreach ($arrFields as $arrField) {
                if ('binary' === $arrField['type']) {
                    $usage = $this->find($strTable, $arrField['name'], $objModel->uuid);
                    if (!empty($usage)) {
                        $arrUsages[$strTable] = $usage;
                    }
                } elseif ('blob' === $arrField['type']) {
                    $usage = $this->find($strTable, $arrField['name'], StringUtil::binToUuid($objModel->uuid));
                    if (!empty($usage)) {
                        $arrUsages[$strTable] = $usage;
                    }
                }
            }
        }

        $objTemplate->usage = $arrUsages;
        $objTemplate->icon = $objFile->icon;
        $objTemplate->mime = $objFile->mime;
        $objTemplate->ctime = Date::parse(Config::get('datimFormat'), $objFile->ctime);
        $objTemplate->mtime = Date::parse(Config::get('datimFormat'), $objFile->mtime);
        $objTemplate->atime = Date::parse(Config::get('datimFormat'), $objFile->atime);
        $objTemplate->path = StringUtil::specialchars($this->strFile);
        $objTemplate->theme = Backend::getTheme();
        $objTemplate->base = Environment::get('base');
        $objTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $objTemplate->title = StringUtil::specialchars($this->strFile);
        if (version_compare(VERSION, '4.9', '>=')) {
            $objTemplate->host = Backend::getDecodedHostname();
        } else {
            $objTemplate->host = static::getDecodedHostname();
        }
        $objTemplate->charset = Config::get('characterSet');
        $objTemplate->labels = (object) $GLOBALS['TL_LANG']['MSC'];
        $objTemplate->download = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['fileDownload']);

        return $objTemplate->getResponse();
    }

    /**
     * Get a Name for a given key form global language configuration.
     *
     * @return string the name
     */
    public static function getUsageName(string $strEntry): string
    {
        if ('tl_news' === $strEntry) {
            // FIXME: Contao maps the table `tl_news` to `Artikel` in german
            $strEntry = 'news';
        }

        // get the name from global modules name
        if (($name = static::getLanguageEntry($strEntry))) {
            return $name;
        }

        // Try to infer the Name
        if ('tl_' === substr($strEntry, 0, 3)) {
            $do = substr($strEntry, 3);

            if ('theme' === $do) {
                $do = 'themes';
            }

            if (($name = static::getLanguageEntry($do))) {
                return $name;
            }

            if (false !== ($pos = strpos($do, '_', 3))) {
                $do = substr($do, 0, $pos);
                if (($name = static::getLanguageEntry($do))) {
                    return $name;
                }
            }
        }

        // No Name Found
        return $GLOBALS['TL_LANG']['tl_files']['custom_usage'];
    }

    /**
     * Return the decoded host name.
     *
     * @return string
     */
    public static function getDecodedHostname()
    {
        $host = Environment::get('host');

        if (0 === strncmp($host, 'xn--', 4)) {
            $host = Idna::decode($host);
        }

        return $host;
    }

    /**
     * Try to get the key from the default Contao Modules.
     *
     * @return string|null the language entry or `false` if not found
     */
    protected static function getLanguageEntry(string $strEntry): ?string
    {
        $modules = ['MOD', 'FMD', 'CTE'];
        foreach ($modules as $module) {
            if (isset($GLOBALS['TL_LANG'][$module][$strEntry])) {
                if (\is_array($GLOBALS['TL_LANG'][$module][$strEntry])) {
                    return $GLOBALS['TL_LANG'][$module][$strEntry][0];
                }
                if (\is_string($GLOBALS['TL_LANG'][$module][$strEntry])) {
                    return $GLOBALS['TL_LANG'][$module][$strEntry];
                }
            }
        }

        return null;
    }

    /**
     * Find records by various criteria.
     *
     * @param mixed $strTable   The database table
     * @param mixed $strColumn  The property name
     * @param mixed $varValue   The property value
     * @param array $arrOptions An optional options array
     *
     * @return array An array of models found
     */
    protected function find($strTable, $strColumn, $varValue, array $arrOptions = [])
    {
        $arrOptions = array_merge(
            [
                'table' => $strTable,
                'column' => $strColumn,
                'value' => $varValue,
            ],
            $arrOptions
        );

        $strQuery = static::buildFindQuery($arrOptions);

        /** @var Statement $objStatement */
        $objStatement = $this->database->prepare($strQuery);

        /** @var Result $objResult */
        $objResult = $objStatement->execute($arrOptions['value']);

        $arrModels = static::createCollectionFromDbResult($objResult, $strTable)->getModels();

        if ('tl_content' === $strTable) {
            $arrParentModels = [];

            foreach ($arrModels as $objModel) {
                /** @var string $strModel */
                $strModel = Model::getClassFromTable($objModel->ptable);

                if (class_exists($strModel)) {
                    /** @var Model $objParentModel */
                    $objParentModel = $strModel::findById($objModel->pid);

                    if (null !== $objParentModel) {
                        // generate backend urls to edit the object
                        $objParentModel->backendUrl = static::buildBackendUrl($objParentModel, $strTable, $objModel->id);

                        // add the model to the array of models
                        $arrParentModels[] = $objParentModel;
                    }
                }
            }

            return $arrParentModels;
        }

        // generate backend urls to edit the objects
        foreach ($arrModels as $objModel) {
            $objModel->backendUrl = static::buildBackendUrl($objModel, $strTable, $objModel->id);
        }

        return $arrModels;
    }

    /**
     * Generate a route to edit the object.
     *
     * @param Model  $objModel The model object
     * @param string $strTable The table name where this model is used
     * @param string $id       The id of the object to edit
     *
     * @return string A backend route
     */
    protected static function buildBackendUrl(Model $objModel, string $strTable, string $id): string
    {
        /** @var string $strModel */
        $strModel = \get_class($objModel);
        $do = $strModel::getTable();

        if ('tl_' === substr($do, 0, 3)) {
            $do = substr($do, 3);

            if (false !== ($pos = strpos($do, '_', 3))) {
                $do = substr($do, 0, $pos);
            }
        } else {
            return '';
        }

        if ('theme' === $do || 'module' === $do) {
            $do = 'themes';
        }

        $container = System::getContainer();

        return $container->get('router')->generate('contao_backend', [
            'do' => $do,
            'act' => 'edit',
            'table' => $strTable,
            'id' => $id,
            'ref' => $container->get('request_stack')->getCurrentRequest()->attributes->get('_contao_referer_id'),
            'rt' => REQUEST_TOKEN,
        ]);
    }

    /**
     * Build a query based on the given options.
     *
     * @param array $arrOptions The options array
     *
     * @return string The query string
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        return QueryBuilder::find($arrOptions);
    }

    /**
     * Create a new collection from a database result.
     *
     * @param Result $objResult The database result object
     * @param string $strTable  The table name
     *
     * @return Collection The model collection
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable)
    {
        return Collection::createFromDbResult($objResult, $strTable);
    }
}
