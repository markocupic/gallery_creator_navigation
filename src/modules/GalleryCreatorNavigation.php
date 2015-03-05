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
namespace MCupic\GalleryCreator;


/**
 * Class GalleryCreatorNavigation
 *
 *
 * @copyright  Marko Cupic 2015
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    GalleryCreatorNavigation
 */
class GalleryCreatorNavigation extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_gallery_creator_navigation';

    /**
     * Trail
     * @var array
     */
    protected $arrTrail;

    /**
     * rootAlbum (id)
     * @var integer
     */
    protected $rootAlbum;

    /**
     * Do not display the module in certain cases
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['navigation'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }


        $objAlbum = \GalleryCreatorAlbumsModel::findByPid(0);
        if ($objAlbum === null)
        {
            return '';
        }

        return parent::generate();
    }

    /**
     * generate module
     */
    protected function compile()
    {

        // Set root
        $this->rootAlbum = $this->gc_rootAlbum > 0 ? $this->gc_rootAlbum : 0;

        // Get trail
        $this->arrTrail = array();
        if (\Input::get('items') != '')
        {
            $activeAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
            if ($activeAlbum !== null)
            {
                $AlbumId = $activeAlbum->id;
                $this->arrTrail = $this->getTrail($AlbumId);
            }
        }

        // render navigation markup
        $this->Template->items = $this->renderNavigation($this->rootAlbum);
    }


    /**
     * render navigation markup
     * @param int $rootAlbum
     * @return string
     */
    protected function renderNavigation($rootAlbum)
    {
        global $objPage;
        $items = array();

        // Layout template fallback
        if ($this->navigationTpl == '')
        {
            $this->navigationTpl = 'nav_gallery_creator_default';
        }

        $objTemplate = new \FrontendTemplate($this->navigationTpl);

        // Get sibling pid
        $siblingPid = false;
        $objSelectedAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
        if ($objSelectedAlbum !== null)
        {
            $siblingPid = $objSelectedAlbum->pid;
        }

        $objAlbum = \GalleryCreatorAlbumsModel::findByPid($rootAlbum);
        if ($objAlbum !== null)
        {
            while ($objAlbum->next())
            {
                $arrClass = array();

                // Do not list albums if $stopLevel <= then the album level
                $level = $this->getAlbumLevel($objAlbum->id);
                if ($this->gc_stopLevel > 0 && $level >= $this->gc_stopLevel)
                {
                    continue;
                }

                $row = $objAlbum->row();
                $row['isActive'] = false;

                // Mark trail items
                if (in_array($objAlbum->id, $this->arrTrail))
                {
                    $arrClass[] = 'trail';
                }

                // Mark items with same level
                if ($objAlbum->alias != \Input::get('items') && isset($siblingPid) && $objAlbum->pid == $siblingPid)
                {
                    $arrClass[] = 'sibling';
                }

                // Mark active items
                if ($objAlbum->alias == \Input::get('items'))
                {
                    $arrClass[] = 'active';
                    $row['isActive'] = true;
                }

                // Title ans link attribute
                $row['title'] = specialchars($objAlbum->name, true);
                $row['link'] = $objAlbum->name;

                // Generate url
                $oPage = \PageModel::findByPk($objPage->id);
                $row['href'] = $oPage->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias, $objPage->language);
                if ($this->jumpTo > 0)
                {
                    $oPage = \PageModel::findByPk($this->jumpTo);
                    if ($oPage !== null)
                    {
                        $row['href'] = $oPage->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias, $objPage->language);
                    }
                }

                // Check for subalbums and store the html markup in $row['subitems']
                $subitems = '';
                $objSubalbums = \GalleryCreatorAlbumsModel::findByPid($objAlbum->id);
                if ($objSubalbums !== null)
                {
                    if ($objSubalbums !== null)
                    {
                        $arrClass[] = 'submenu';
                    }
                    $subitems = $this->renderNavigation($objAlbum->id);
                }
                // Add subitems
                $row['subitems'] = $subitems;

                // Add Class attribute
                $row['class'] = implode(' ', $arrClass);

                // store item in $items
                $items[] = $row;
            }

        }

        // Add classes first and last
        if (!empty($items))
        {
            $last = count($items) - 1;
            $objTemplate->level = 'level_' . $level;
            $items[0]['class'] = trim($items[0]['class'] . ' first');
            $items[$last]['class'] = trim($items[$last]['class'] . ' last');
        }

        $objTemplate->items = $items;

        return !empty($items) ? $objTemplate->parse() : '';

    }

    /**
     * get the level of an album
     * @param $albumId
     * @return int
     */
    public function getAlbumLevel($albumId)
    {
        $level = 0;
        $pid = \GalleryCreatorAlbumsModel::findByPk($albumId)->pid;
        while ($pid > 0)
        {
            $level++;
            $pid = \GalleryCreatorAlbumsModel::findByPk($pid)->pid;
        }

        return $level;
    }


    /**
     * Get the parent album trail as an array
     * @param $AlbumId
     * @return array
     */
    public function getTrail($AlbumId)
    {

        $arrTrail = array();
        $objAlb = \GalleryCreatorAlbumsModel::findByPk($AlbumId);
        if ($objAlb !== null)
        {
            $pid = $objAlb->pid;
            while ($pid > 0)
            {
                $parentAlb = \GalleryCreatorAlbumsModel::findByPk($pid);
                if ($parentAlb !== null)
                {
                    $arrTrail[] = $parentAlb->id;
                    $pid = $parentAlb->pid;
                }
            }
        }

        return $arrTrail;
    }

}