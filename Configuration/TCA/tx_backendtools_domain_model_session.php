<?php
return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session',
		'label' => 'action',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'enablecolumns' => array(

		),
		'searchFields' => 'action',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('backendtools') . 'Resources/Public/Icons/tx_backendtools_domain_model_session.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'action, value1, value2, value3, value4, value5, value6, pageel, beuser',
	),
	'types' => array(
		'1' => array('showitem' => 'action, value1, value2, value3, value4, value5, value6, pageel, beuser'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'action' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.action',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'value1' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value1',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'value2' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value2',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'value3' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value3',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'value4' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value4',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'value5' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value5',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'value6' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.value6',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'pageel' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.pageel',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'beuser' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang_db.xlf:tx_backendtools_domain_model_session.beuser',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'be_users',
				'minitems' => 0,
				'maxitems' => 1,
				'appearance' => array(
					'collapseAll' => 0,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		
	),
);