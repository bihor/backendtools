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
  'description' => '8 admin tools: extension-list, slug vs. RealURL, redirects import & check, file-deletion, images with no alt or title, unzip and link-list.',
  'category' => 'module',
  'author' => 'Kurt Gusbeth',
  'author_company' => 'fixpunkt werbeagentur gmbh',
  'author_email' => 'info@quizpalme.de',
  'state' => 'stable',
  'uploadfolder' => false,
  'createDirs' => '',
  'clearCacheOnLoad' => false,
  'version' => '2.0.11',
  'constraints' => 
  array (
    'depends' => array (
        'typo3' => '9.5.20-10.4.99',
    ),
  ),
);