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
	 * Get list of pages/elements with extensions
	 * @param	int		$my_c			content visibility
	 * @param	int		$my_p			pages visibility
	 * @param	int		$my_type		type
	 * @param	string	$my_value		type value
	 * @param	string	$my_flexform	flexform value
	 * @param	string	$my_exclude		exclude type
	 * @return array
	 */
	public function getPagesWithExtensions($my_c, $my_p, $my_type, $my_value, $my_flexform, $my_exclude) {
		$pages = [];
		$domains = $this->getDomains();
		$PageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$exclude_ctypes = [
			"html", "list", "text", "image", "textpic", "textmedia", "bullets", "menu",
			"search", "mailform", "indexed_search", "login", "header", "rte",
			"table", "splash", "uploads", "multimedia", "media", "script",
			"shortcut", "div"
		];
		if ($my_exclude) {
			$exclude_ctypes = array_merge ($exclude_ctypes, explode(' ', $my_exclude));
		}
		
		// Query aufbauen
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'tt_content.uid',
			'tt_content.pid',
			'tt_content.deleted AS ttdeleted',
			'tt_content.hidden AS tthidden',
			'tt_content.header',
			'tt_content.sys_language_uid',
			'tt_content.CType',
			'tt_content.list_type',
			'tt_content.pi_flexform',
			'pages.title',
			'pages.deleted AS pdeleted',
			'pages.hidden AS phidden'
		]) -> from ('tt_content')
		-> join(
			'tt_content',
			'pages',
			'pages',
			$queryBuilder->expr()->eq('tt_content.pid', $queryBuilder->quoteIdentifier('pages.uid'))
			);
		
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
		
		if ($my_c==1) {
			$res -> andWhere("tt_content.deleted=1 OR tt_content.hidden=1"); //ToDO: Schöner machen
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tt_content.deleted', 0),
				$queryBuilder->expr()->eq('tt_content.hidden', 0)
			]);
		}
		if ($my_p==1) {
			$res -> andWhere("pages.deleted=1 OR pages.hidden=1"); //ToDO: Schöner machen
		} else if ($my_p==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('pages.deleted', 0),
				$queryBuilder->expr()->eq('pages.hidden', 0)
			]);
		}
		
		// Das Haupt-Where
		if ($my_value) {
			if ($my_type == 2) {
				$res -> andWhere(
					$queryBuilder->expr()->like('tt_content.CType', $queryBuilder->createNamedParameter($my_value."%"))
				);
			} else if ($my_type == 1) {
				$res -> andWhere(
					$queryBuilder->expr()->like('tt_content.list_type', $queryBuilder->createNamedParameter($my_value."%"))
				);
			}
		} else {
			//ToDo: hier kommt eine Art Array zurück 
			/*$exclude_ctypes = $queryBuilder->createNamedParameter(
                '"'.
				implode('","', 	$exclude_ctypes).
				'"');*/
			$exclude_ctypes =	'"'.implode('","', 	$exclude_ctypes).'"';
			$res -> andWhere('
            (
                tt_content.list_type!="" AND tt_content.list_type != "0"
            )
            OR tt_content.CType NOT IN ('.$exclude_ctypes.')');
		}
		
		if ($my_flexform) {
			$res -> andWhere(
				$queryBuilder->expr()->like('tt_content.pi_flexform', $queryBuilder->createNamedParameter("%".$my_flexform."%"))
			);
		}
		
		//print_r($queryBuilder->getSQL());
		//echo "<pre>";
		
		$result = $res -> orderBy('tt_content.pid')
		-> addOrderBy('tt_content.sorting')
		-> execute();
		//print_r($queryBuilder->getParameters());
		foreach($result as $row) {
			$subject = $row['pi_flexform'];
			$pattern = '/<field index="switchableControllerActions">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
			$matches = array();
			preg_match($pattern, $subject, $matches);
			if ($matches[2]) {
				$row['actions'] = str_replace('###', '&gt;', str_replace(';', ', ', str_replace('&gt;', '###', $matches[2])));
			} else {
				$pattern = '/<field index="what_to_display">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
				$matches = array();
				preg_match($pattern, $subject, $matches);
				if ($matches[2]) {
					$row['actions'] = $matches[2];
				}
			}
			$root = array_pop($PageRepository->getRootLine($row['pid']));
			$row['root'] = $root['uid'];
			$row['domain'] = $domains[$root['uid']];
			$row['csvtitle'] = str_replace(';', ',', str_replace('"', '', $row['title']));
			$pages[] = $row;
		}
		return $pages;
	}
	
	/**
	 * Bilder ohne Alt- oder Titel-Tag
	 *
	 * @param   integer   Modus
	 *
	 * @return  array     Bilder
	 */
	function getImagesWithout($img_without) {
		$pageRep = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$domains = $this->getDomains();
		$fileArray = array();
		$fileOrder = array();
		$finalArray = array();
		
		// sys_file: get images
		$table = 'sys_file';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		$statement = $queryBuilder
			->select('*')
			->from($table)
			->where($queryBuilder->expr()->like('mime_type', '"image%"'))
			->orderBy('name', 'ASC')
			->execute();
		while ($row = $statement->fetch()) {
			$uid = $row['uid'];
			$fileOrder[] = $uid;
			$fileArray[$uid] = $row;
		}
		
		// sys_file_metadata
		$table = 'sys_file_metadata';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		$statement = $queryBuilder
			->select('*')
			->from($table)
			->execute();
		while ($row = $statement->fetch()) {
			$uid = $row['file'];
			if ($fileArray[$uid]['uid'] == $uid) {
				$fileArray[$uid]['meta_title'] = $row['title'];
				$fileArray[$uid]['meta_alt'] = $row['alternative'];
				$fileArray[$uid]['meta_width'] = $row['width'];
				$fileArray[$uid]['meta_height'] = $row['height'];
			}
		}
		
		// sys_file_reference und tt_content
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'sys_file_reference.uid',
			'sys_file_reference.title',
			'sys_file_reference.alternative',
			'sys_file_reference.uid_local',
			'sys_file_reference.uid_foreign',
			'tt_content.pid AS tt_pid',
			'tt_content.sys_language_uid AS tt_lang'
		]) -> from ('sys_file_reference')
		-> join(
			'sys_file_reference',
			'tt_content',
			'tt_content',
			$queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->quoteIdentifier('tt_content.uid'))
			);
		$res -> andWhere('sys_file_reference.tablenames="tt_content"');
		//print_r($queryBuilder->getSQL());
		$result = $res -> execute();
		foreach($result as $row) {
			$uid = $row['uid_local'];
			if ($fileArray[$uid]['uid'] == $uid) {
				$fileArray[$uid]['ref_title'] = $row['title'];
				$fileArray[$uid]['ref_alt'] = $row['alternative'];
				$fileArray[$uid]['cid'] = $row['uid_foreign'];
				$fileArray[$uid]['tt_pid'] = $row['tt_pid'];
				$fileArray[$uid]['tt_lang'] = $row['tt_lang'];
				//echo "uid $uid <br>\n";
			}
		}
		
		foreach ($fileOrder as $uid) {
			$imgArray = $fileArray[$uid];
			if (((($img_without == 1) || ($img_without == 3)) && (($imgArray['meta_alt']=='') && ($imgArray['ref_alt']==''))) ||
				((($img_without == 2) || ($img_without == 3)) && (($imgArray['meta_title']=='') && ($imgArray['ref_title']==''))) ||
				((($img_without == 4) || ($img_without == 6)) && (($imgArray['meta_alt']!='') || ($imgArray['ref_alt']!=''))) ||
				((($img_without == 5) || ($img_without == 6)) && (($imgArray['meta_title']!='') || ($imgArray['ref_title']!='')))) {
					// TODO: zu wenig Bilder mit alt!
					$root = array_pop($pageRep->getRootLine($imgArray['tt_pid']));
					$imgArray['root'] = $root['uid'];
					$imgArray['domain'] = $domains[$root['uid']];
					$finalArray[] = $imgArray;
				}
		}
		return $finalArray;
	}
	
	/**
	 * Get list of domains
	 *
	 * @return array
	 */
	public function getDomains() {
		$domains = array();
		$table = 'sys_domain';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		$statement = $queryBuilder
			->select('*')
			->from($table)
		//	->where($queryBuilder->expr()->eq('hidden', 0))
			->orderBy('sorting', 'DESC')
			->execute();
		while ($row = $statement->fetch()) {
			// gibt es nicht mehr:
			//if ($row['redirectTo']) {
			//	$domain = $row['redirectTo'];
			//} else {
				$domain = $row['domainName'];
			//}
			if (substr($domain, 0, 4) != 'http') {
				$domain = 'http://' . $domain;
			}
			if (substr($domain, -1) == '/') {
				$domain = substr($domain, 0, -1);
			}
			$domains[$row['pid']] = $domain;
		}
		return $domains;
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