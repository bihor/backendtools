<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "backendtools".
 *
 * Auto generated 18-01-2018 10:57
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Backend tools',
  'description' => '6 admin tools for extensions, redirects, files, images and links: extensionlist, redirects import, filedeletion, images with no title, unzip and linklist. Still depends on typo3db_legacy.',
  'category' => 'module',
	'author' => 'Kurt Gusbeth',
	'author_company' => 'fixpunkt werbeagentur gmbh',
  'author_email' => 'info@quizpalme.de',
  'state' => 'stable',
  'uploadfolder' => false,
  'createDirs' => '',
  'clearCacheOnLoad' => false,
  'version' => '1.3.3',
  'constraints' => 
  array (
    'depends' => array (
        'typo3' => '9.5.0-9.5.99',
    	'typo3db_legacy' => '1.0.0-1.9.99',
    ),
  ),
);

