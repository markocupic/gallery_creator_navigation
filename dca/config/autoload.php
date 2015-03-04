<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Gallery_creator_navigation
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'GalleryCreator',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'GalleryCreator\GalleryCreatorNavigation' => 'system/modules/gallery_creator_navigation/modules/GalleryCreatorNavigation.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'nav_gallery_creator_default'    => 'system/modules/gallery_creator_navigation/templates',
	'mod_gallery_creator_navigation' => 'system/modules/gallery_creator_navigation/templates',
));
