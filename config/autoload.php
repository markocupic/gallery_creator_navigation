<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MCupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Src
	'MCupic\GalleryCreator\GalleryCreatorNavigation' => 'system/modules/gallery_creator_navigation/src/modules/GalleryCreatorNavigation.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_gallery_creator_navigation' => 'system/modules/gallery_creator_navigation/templates',
	'nav_gallery_creator_default'    => 'system/modules/gallery_creator_navigation/templates',
));
