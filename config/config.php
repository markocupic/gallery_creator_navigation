<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2013 Leo Feyer
 * @package GalleryCreatorNavigation
 * @author    Marko Cupic
 * @license   GNU/LGPL
 * @copyright Marko Cupic 2014
 */

array_insert(
    $GLOBALS['FE_MOD'],
    2,
    array('module_type_gallery_creator' => array(
        'gallery_creator_navigation' => 'MCupic\GalleryCreator\GalleryCreatorNavigation'
    )
));
