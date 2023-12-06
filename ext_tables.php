<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function()
	{
		
		if (TYPO3_MODE === 'BE') {
			
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
				'Backendtools',
				'tools', // Make module a submodule of 'tools'
				'mod1', // Submodule key
				'', // Position
				[
                    \Fixpunkt\Backendtools\Controller\SessionController::class => 'list, latest, layouts, filedeletion, missing, images, pagesearch, redirects, redirectscheck'
				],
				[
					'access' => 'user,group',
					'icon'   => 'EXT:backendtools/Resources/Public/Icons/user_mod_mod1.gif',
					'labels' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_mod1.xlf'
				]
			);
		}

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_backendtools_domain_model_session', 'EXT:backendtools/Resources/Private/Language/locallang_csh_tx_backendtools_domain_model_session.xlf');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_backendtools_domain_model_session');
	}
);
