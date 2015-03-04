<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2013 Leo Feyer
 * @package GalleryCreatorNavigation
 * @author    Marko Cupic
 * @license   GNU/LGPL
 * @copyright Marko Cupic 2014
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace GalleryCreator;


/**
 * Class ModuleNavigation
 *
 * Front end module "navigation".
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class GalleryCreatorNavigation extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_gallery_creator_navigation';

    protected $arrTrail;

    /**
     * Do not display the module in certain cases
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['navigation'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // if(TL_MODE == 'FE'):
        $this->arrTrail = array();


        // set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }


        $objAlbum = \GalleryCreatorAlbumsModel::findByPid(0);
        if ($objAlbum === null) {
            return '';
        }


        // Find trail
        if (\Input::get('items') != '') {
            $activeAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
            if ($activeAlbum !== null) {
                $AlbumId = $activeAlbum->id;
                while ($parent = $this->findParentAlbum($AlbumId)) {
                    $this->arrTrail[] = $parent;
                    $AlbumId = $parent;
                }
            }
        }

        // Do not show menu in certain cases
        if ($this->levelOffset > 0 && !\Input::get('items')) {
            return '';
        }
        if ($this->levelOffset > 0 && \Input::get('items')) {
            $arrTrail = array_reverse($this->arrTrail);
            if (!isset($arrTrail[$this->levelOffset - 1])) {
                return '';
            }
        }
        return parent::generate();
    }

    /**
     * generate module
     */
    protected function compile()
    {

        $pid = 0;
        $level = 1;

        // change $pid and $level, if $this->levelOffset is > 0 and if there is an active album
        if ($this->levelOffset > 0 && \Input::get('items')) {
            $arrTrail = array_reverse($this->arrTrail);
            if (isset($arrTrail[$this->levelOffset - 1])) {
                $pid = $arrTrail[$this->levelOffset - 1];
                $level = $this->levelOffset + 1;
            }
        }

        // render navigation markup
        $this->Template->items = $this->renderGcNavigation($pid, $level);
    }


    /**
     * render navigation markup
     * @param int $pid
     * @param int $level
     * @return string
     */
    protected function renderGcNavigation($pid = 0, $level = 1)
    {
        global $objPage;
        $items = array();

        // Layout template fallback
        if ($this->navigationTpl == '') {
            $this->navigationTpl = 'nav_gallery_creator_default';
        }

        $objTemplate = new \FrontendTemplate($this->navigationTpl);
        $objTemplate->level = 'level_' . $level++;

        // Get sibling pid
        $siblingPid = false;
        $objSelectedAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
        if ($objSelectedAlbum !== null) {
            $siblingPid = $objSelectedAlbum->pid;
        }

        $objAlbum = \GalleryCreatorAlbumsModel::findByPid($pid);
        if ($objAlbum !== null) {
            while ($objAlbum->next()) {
                $row = $objAlbum->row();
                $row['isActive'] = false;

                // Mark trail items
                $strClass = in_array($objAlbum->id, $this->arrTrail) ? ' trail' : '';

                // Mark items with same level
                $strClass .= ($objAlbum->alias != \Input::get('items') && isset($siblingPid) && $objAlbum->pid == $siblingPid) ? ' sibling' : '';

                // Mark active items
                if ($objAlbum->alias == \Input::get('items')) {
                    $strClass .= ' active';
                    $row['isActive'] = true;
                }

                $row['title'] = specialchars($objAlbum->name, true);
                $row['link'] = $objAlbum->name;

                // Generate url
                $row['href'] = \Controller::generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias, $objPage->language);
                if ($this->rootPage > 0) {
                    $oPage = \PageModel::findByPk($this->rootPage);
                    if ($oPage !== null) {
                        $row['href'] = \Controller::generateFrontendUrl($oPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias, $objPage->language);
                    }
                }


                // Check for subalbums and store the html markup in $row['subitems']
                $subitems = '';
                $objSubalbum = \GalleryCreatorAlbumsModel::findByPid($objAlbum->id);
                if ($objSubalbum !== null) {
                    $strClass = $objSubalbum !== null ? ' submenu' : '';
                    $subitems = $this->renderGcNavigation($objAlbum->id, $level);
                }
                $row['subitems'] = $subitems;


                $row['class'] = trim($strClass);

                // store item in $items
                $items[] = $row;
            }

        }

        // Add classes first and last
        if (!empty($items)) {
            $last = count($items) - 1;

            $items[0]['class'] = trim($items[0]['class'] . ' first');
            $items[$last]['class'] = trim($items[$last]['class'] . ' last');
        }

        $objTemplate->items = $items;
        return !empty($items) ? $objTemplate->parse() : '';

    }

    /**
     * find the parent album
     * param $id
     * @return bool
     */
    protected function findParentAlbum($id)
    {
        $objAlbum = \GalleryCreatorAlbumsModel::findByPk($id);
        if ($objAlbum->pid > 0) {
            return $objAlbum->pid;
        }
        return false;
    }

}