<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
	function()
	{
		
		if (TYPO3_MODE === 'BE') {
			
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
				'Fixpunkt.Backendtools',
				'tools', // Make module a submodule of 'tools'
				'mod1', // Submodule key
				'', // Position
				[
					'Session' => 'list, latest, filedeletion, images, pagesearch, redirects, redirectscheck, realurl',
				],
				[
					'access' => 'user,group',
					'icon'   => 'EXT:backendtools/ext_icon.gif',
					'labels' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_mod1.xlf',
				]
			);
		}

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_backendtools_domain_model_session', 'EXT:backendtools/Resources/Private/Language/locallang_csh_tx_backendtools_domain_model_session.xlf');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_backendtools_domain_model_session');
	}
);
