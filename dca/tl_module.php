<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2013 Leo Feyer
 * @package GalleryCreatorNavigation
 * @author    Marko Cupic
 * @license   GNU/LGPL
 * @copyright Marko Cupic 2014
 */

// palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator_navigation'] = '{title_legend},name,headline,type;{nav_legend},gc_rootAlbum,gc_stopLevel;{jump_to_legend:hide},jumpTo;{template_legend:hide},gcNavigationTemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// fields
$GLOBALS['TL_DCA']['tl_module']['fields']['gcNavigationTemplate'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['navigationTpl'],
    'exclude' => true, 'inputType' => 'select',
    'options_callback' => array('tl_gallery_creator_navigation_module', 'getNavigationTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(64) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['gc_rootAlbum'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_rootAlbum'],
    'inputType'        => 'select',
    'exclude'          => true,
    'foreignKey'       => 'tl_gallery_creator_albums.name',
    'eval'             => array('includeBlankOption' => true, 'multiple' => false, 'tl_class' => 'clr'),
    'sql'              => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_stopLevel'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['gc_stopLevel'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => range(0, 100, 1),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

/**
 * Class tl_gallery_creator_navigation_module
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @copyright Marko Cupic 2014
 * @package    gallery_creator_navigation
 */
class tl_gallery_creator_navigation_module extends Backend
{

       /**
        * Return all navigation templates as array
        * @return array
        */
       public function getNavigationTemplates()
       {
              return $this->getTemplateGroup('nav_gallery_creator_');
       }
}