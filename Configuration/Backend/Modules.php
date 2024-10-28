<?php

use Fixpunkt\Backendtools\Controller\SessionController;

/**
 * Definitions for modules provided by EXT:examples
 */
return [
    'backendtools' => [
        'parent' => 'tools',
        'position' => ['after' => '*'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'iconIdentifier' => 'extension-backendtools-module',
        'path' => '/module/tools/mod1',
        'labels' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_mod1.xlf',
        'extensionName' => 'Backendtools',
        'controllerActions' => [
            SessionController::class => 'list, latest, layouts, missing, images, pagesearch, redirects, redirectscheck',
        ],
    ],
];
