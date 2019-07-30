<?php
namespace Fixpunkt\Backendtools\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kurt Gusbeth <info@quizpalme.de>
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Session Repository
 *
 * @package backendtools
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SessionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
	
	/**
	 * findByAction ersetzen, wegen user-id-Abfrage
	 * @param	string	$action		Action
	 * @param	int		$beuser		BE-user-ID
	 */
	public function findByAction($action, $beuser) {
		$constraints = array();
		$query = $this->createQuery();
		$constraints[] = $query->equals('action', $action);
		$constraints[] = $query->equals('beuser', $beuser);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}
	
	
	/**
	 * addRedirect
	 * @param	string	$from	from link
	 * @param	string	$to		to link
	 * @param	int		$regexp	regular expression?
	 * @param	int		$statuscode	Statuscode, e.g. 301
	 * @param	int		$createdby	Created by BE-user
	 */
	public function addRedirect($from, $to, $regexp, $statuscode, $createdby) {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_redirect');
		return $queryBuilder
			->insert('sys_redirect')
			->values([
				'source_path' => $from,
				'target' => $to,
				'is_regexp' => $regexp,
				'target_statuscode' => $statuscode,
				'updatedon' => time(),
				'createdon' => time(),
				'createdby' => $createdby,
				'source_host' => '*'
			])
			->execute();
	}
}
?>