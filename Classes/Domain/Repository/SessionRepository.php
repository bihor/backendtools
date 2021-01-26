<?php
namespace Fixpunkt\Backendtools\Domain\Repository;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

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
class SessionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Site finder
     *
     * @var \TYPO3\CMS\Core\Site\SiteFinder
     */
    protected $siteFinder = null;
    
	/**
	 * findByAction ersetzen, wegen user-id-Abfrage
	 * 
	 * @param	string	$action		Action
	 * @param	int		$beuser		BE-user-ID
	 */
	public function findByAction($action, $beuser)
	{
		$constraints = array();
		$query = $this->createQuery();
		$constraints[] = $query->equals('action', $action);
		$constraints[] = $query->equals('beuser', $beuser);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}
	
	
	
	/**
	 * Get list of pages/elements with extensions
	 * 
	 * @param	int		$my_c			content visibility
	 * @param	int		$my_p			pages visibility
	 * @param	int		$my_type		type
	 * @param	string	$my_value		type value
	 * @param	string	$my_flexform	flexform value
	 * @param	string	$my_exclude		exclude type
	 * @return array
	 */
	public function getPagesWithExtensions($my_c, $my_p, $my_type, $my_value, $my_flexform, $my_exclude)
	{
		$pages = [];
		//$PageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
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
			'tt_content.colPos',
			'tt_content.deleted AS ttdeleted',
			'tt_content.hidden AS tthidden',
			'tt_content.header',
			'tt_content.sys_language_uid',
			'tt_content.CType',
			'tt_content.list_type',
			'tt_content.pi_flexform',
			'pages.title',
		    'pages.slug',
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
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('tt_content.deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('tt_content.hidden', $queryBuilder->createNamedParameter(1))
				)
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tt_content.deleted', $queryBuilder->createNamedParameter(0)),
				$queryBuilder->expr()->eq('tt_content.hidden', $queryBuilder->createNamedParameter(0))
			]);
		}
		if ($my_p==1) {
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1))
					)
			]);
		} else if ($my_p==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
				$queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0))
			]);
		}
		
		// Das Haupt-Where
		if ($my_value) {
			if ($my_type == 2) {
				$res -> andWhere(
					$queryBuilder->expr()->like('tt_content.CType', $queryBuilder->createNamedParameter($queryBuilder->escapeLikeWildcards($my_value) . "%"))
				);
			} else if ($my_type == 1) {
				$res -> andWhere(
					$queryBuilder->expr()->like('tt_content.list_type', $queryBuilder->createNamedParameter($queryBuilder->escapeLikeWildcards($my_value) . "%"))
				);
			}
		} else {
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->neq('tt_content.list_type', $queryBuilder->createNamedParameter('')),
						$queryBuilder->expr()->neq('tt_content.list_type', $queryBuilder->createNamedParameter('0'))
					),
					$queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($exclude_ctypes, Connection::PARAM_STR_ARRAY))
				)
			]);
		}
		
		if ($my_flexform) {
			$res -> andWhere(
				$queryBuilder->expr()->like('tt_content.pi_flexform', $queryBuilder->createNamedParameter("%" . $queryBuilder->escapeLikeWildcards($my_flexform) . "%"))
			);
		}
		
		//print_r($res->getSQL());
		
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
			if ( $row["pdeleted"] ) {
			    $row['domain'] = '';
			} else {
			    $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
			}
			$row['csvheader'] = str_replace('"', '\'', $row['header']);
			$row['csvtitle'] = str_replace('"', '\'', $row['title']);
			$pages[] = $row;
		}
		return $pages;
	}
	
	/**
	 * Finde Elemente mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getPageLinks($my_c, $my_p, $linkto_uid)
	{
		$finalArray = [];
		$referenceArray = [];
		$this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
		
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference')->createQueryBuilder();
		$res = $queryBuilder ->select('uid_foreign') -> from ('sys_file_reference');
		$queryBuilder
		->getRestrictions()
		->removeAll();
		$res->where(
		    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tt_content'))
		);
		$res -> andWhere(...[
		    $queryBuilder->expr()->orX(
		        $queryBuilder->expr()->eq('link', $queryBuilder->createNamedParameter("t3://page?uid=" . $linkto_uid)),
		        $queryBuilder->expr()->like('link', $queryBuilder->createNamedParameter("t3://page?uid=" . $linkto_uid . " %"))
		    )
		]);
		//print_r($res->getSQL());
		$result = $res -> execute();
		//print_r($queryBuilder->getParameters());
		
		foreach($result as $row) {
		    $referenceArray[] = $row['uid_foreign'];
		}
		//var_dump($referenceArray);
		
		// Links in tt_content und sys_file_reference. Query aufbauen
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'tt_content.uid',
			'tt_content.pid',
			'tt_content.colPos',
			'tt_content.deleted AS ttdeleted',
			'tt_content.hidden AS tthidden',
			'tt_content.header',
			'tt_content.sys_language_uid',
			'pages.title',
		    'pages.slug',
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
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('tt_content.deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('tt_content.hidden', $queryBuilder->createNamedParameter(1))
					)
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tt_content.deleted', $queryBuilder->createNamedParameter(0)),
				$queryBuilder->expr()->eq('tt_content.hidden', $queryBuilder->createNamedParameter(0))
			]);
		}
		if ($my_p==1) {
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1))
					)
			]);
		} else if ($my_p==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
				$queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0))
			]);
		}
		
		// Das Haupt-Where
		$res -> andWhere(...[
		    $queryBuilder->expr()->orX(
		        $queryBuilder->expr()->like('tt_content.bodytext', $queryBuilder->createNamedParameter('%"t3://page?uid=' . $linkto_uid . '"%')),
		        $queryBuilder->expr()->eq('tt_content.header_link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
		        $queryBuilder->expr()->like('tt_content.header_link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %')),
		        $queryBuilder->expr()->in('tt_content.uid', $queryBuilder->createNamedParameter($referenceArray, Connection::PARAM_INT_ARRAY))
		    )
		]);
//		$res -> andWhere("tt_content.bodytext LIKE '%\"t3://page?uid=".$linkto_uid."\"%'
// OR tt_content.header_link='t3://page?uid=".$linkto_uid."'
// OR tt_content.header_link LIKE 't3://page?uid=".$linkto_uid." %'
// OR tt_content.uid IN (SELECT uid_foreign FROM sys_file_reference WHERE tablenames='tt_content' AND (link='t3://page?uid=".$linkto_uid."' OR link LIKE 't3://page?uid=".$linkto_uid." %'))");
		//print_r($res->getSQL());
		$result = $res -> orderBy('tt_content.pid')
		-> addOrderBy('tt_content.sorting')
		-> execute();
		
		foreach($result as $row) {
		    if ( $row["pdeleted"] ) {
		        $row['domain'] = '';
		    } else {
		        $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
		    }
			$finalArray[] = $row;
		}
		return $finalArray;
	}
	
	/**
	 * Finde news mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getNewsLinks($my_c, $my_p, $linkto_uid)
	{
		$finalArray = [];
		
		// Links in news. Query aufbauen
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_news_domain_model_news')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'uid',
			'pid',
			'deleted',
			'hidden',
			'title',
			'sys_language_uid'
		]) -> from ('tx_news_domain_model_news');
		
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
		
		if ($my_c==1) {
			$res -> andWhere(...[
			    $queryBuilder->expr()->orX(
			        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(1)),
			        $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(1))
			    )
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('deleted', 0),
				$queryBuilder->expr()->eq('hidden', 0)
			]);
		}
		$res -> andWhere(...[
		    $queryBuilder->expr()->orX(
		        $queryBuilder->expr()->like('bodytext', $queryBuilder->createNamedParameter('%"t3://page?uid=' . $linkto_uid . '"%')),
		        $queryBuilder->expr()->eq('internalurl', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
		        $queryBuilder->expr()->like('internalurl', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %'))
		    )
		]);
		$result = $res -> orderBy('pid', 'ASC')
		-> addOrderBy('tstamp', 'DESC')
		-> execute();
		
		foreach($result as $row) {
			$uid = $row['uid'];
			$finalArray[$uid] = $row;
		}
		
		// images links
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_news_domain_model_news')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'tx_news_domain_model_news.uid',
			'tx_news_domain_model_news.pid',
			'tx_news_domain_model_news.deleted',
			'tx_news_domain_model_news.hidden',
			'tx_news_domain_model_news.title',
			'tx_news_domain_model_news.sys_language_uid'
		]) -> from ('tx_news_domain_model_news')
		-> join(
			'tx_news_domain_model_news',
			'sys_file_reference',
			'ref',
			$queryBuilder->expr()->eq('tx_news_domain_model_news.uid', $queryBuilder->quoteIdentifier('ref.uid_foreign'))
		)
		->where(
		    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tx_news_domain_model_news'))
		);
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
		
		if ($my_c==1) {
			$res -> andWhere(...[
			    $queryBuilder->expr()->orX(
			        $queryBuilder->expr()->eq('tx_news_domain_model_news.deleted', $queryBuilder->createNamedParameter(1)),
			        $queryBuilder->expr()->eq('tx_news_domain_model_news.hidden', $queryBuilder->createNamedParameter(1))
			    )
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tx_news_domain_model_news.deleted', 0),
				$queryBuilder->expr()->eq('tx_news_domain_model_news.hidden', 0)
			]);
		}
		$res -> andWhere(...[
		    $queryBuilder->expr()->orX(
		        $queryBuilder->expr()->eq('ref.link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
		        $queryBuilder->expr()->like('ref.link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %'))
		    )
		]);
		
		$result = $res -> orderBy('tx_news_domain_model_news.pid', 'ASC')
		-> addOrderBy('tx_news_domain_model_news.tstamp', 'DESC')
		-> execute();
		
		foreach($result as $row) {
			$uid = $row['uid'];
			$finalArray[$uid] = $row;
		}
		
		// related links
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_news_domain_model_news')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'tx_news_domain_model_news.uid',
			'tx_news_domain_model_news.pid',
			'tx_news_domain_model_news.deleted',
			'tx_news_domain_model_news.hidden',
			'tx_news_domain_model_news.title',
			'tx_news_domain_model_news.sys_language_uid'
		]) -> from ('tx_news_domain_model_news')
		-> join(
			'tx_news_domain_model_news',
			'tx_news_domain_model_link',
			'tx_news_domain_model_link',
			$queryBuilder->expr()->eq('tx_news_domain_model_news.uid', $queryBuilder->quoteIdentifier('tx_news_domain_model_link.parent'))
		);
			
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
			
		if ($my_c==1) {
		    $res -> andWhere(...[
		        $queryBuilder->expr()->orX(
		            $queryBuilder->expr()->eq('tx_news_domain_model_news.deleted', $queryBuilder->createNamedParameter(1)),
		            $queryBuilder->expr()->eq('tx_news_domain_model_news.hidden', $queryBuilder->createNamedParameter(1))
		        )
		    ]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tx_news_domain_model_news.deleted', 0),
				$queryBuilder->expr()->eq('tx_news_domain_model_news.hidden', 0)
			]);
		}
		//$res -> andWhere("tx_news_domain_model_link.uri='t3://page?uid=".$linkto_uid."' OR tx_news_domain_model_link.uri LIKE 't3://page?uid=".$linkto_uid." %'");
		$res -> andWhere(...[
		    $queryBuilder->expr()->orX(
		        $queryBuilder->expr()->eq('tx_news_domain_model_link.uri', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
		        $queryBuilder->expr()->like('tx_news_domain_model_link.uri', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %'))
		    )
		]);
		$result = $res -> orderBy('tx_news_domain_model_news.pid', 'ASC')
		-> addOrderBy('tx_news_domain_model_news.tstamp', 'DESC')
		-> execute();
			
		foreach($result as $row) {
			$uid = $row['uid'];
			$finalArray[$uid] = $row;
		}
		return $finalArray;
	}
	
	/**
	 * Finde Camaliga-Elemente mit Links zu einer gesuchten Seite
	 *
	 * @param   integer	$my_c: content hidden?
	 * @param	integer	$my_p: page hidden?
	 * @param	integer	$linkto_uid: gesuchte uid
	 *
	 * @return  array     Content-Elemente
	 */
	function getCamaligaLinks($my_c, $my_p, $linkto_uid)
	{
		$finalArray = [];
		
		// Links in camaliga. Query aufbauen
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_camaliga_domain_model_content')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'uid',
			'pid',
			'deleted',
			'hidden',
			'title',
			'sys_language_uid'
		]) -> from ('tx_camaliga_domain_model_content');
		
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
		
		if ($my_c==1) {
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(1))
				)
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('deleted', 0),
				$queryBuilder->expr()->eq('hidden', 0)
			]);
		}
		$res -> andWhere(...[
			$queryBuilder->expr()->orX(
				$queryBuilder->expr()->like('longdesc', $queryBuilder->createNamedParameter('%"t3://page?uid=' . $linkto_uid . '"%')),
				$queryBuilder->expr()->eq('link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
				$queryBuilder->expr()->like('link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %'))
			)
		]);		
		$result = $res -> orderBy('pid', 'ASC')
		-> addOrderBy('tstamp', 'DESC')
		-> execute();
		
		foreach($result as $row) {
			$uid = $row['uid'];
			$finalArray[$uid] = $row;
		}
		
		// image links
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_camaliga_domain_model_content')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
			'tx_camaliga_domain_model_content.uid',
			'tx_camaliga_domain_model_content.pid',
			'tx_camaliga_domain_model_content.deleted',
			'tx_camaliga_domain_model_content.hidden',
			'tx_camaliga_domain_model_content.title',
			'tx_camaliga_domain_model_content.sys_language_uid'
		]) -> from ('tx_camaliga_domain_model_content')
		-> join(
			'tx_camaliga_domain_model_content',
			'sys_file_reference',
			'ref',
			$queryBuilder->expr()->eq('tx_camaliga_domain_model_content.uid', $queryBuilder->quoteIdentifier('ref.uid_foreign'))
		)
		->where(
			$queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tx_camaliga_domain_model_content'))
		);			
		// Restricions
		$queryBuilder
		->getRestrictions()
		->removeAll();
		
		if ($my_c==1) {
			$res -> andWhere(...[
				$queryBuilder->expr()->orX(
					$queryBuilder->expr()->eq('tx_camaliga_domain_model_content.deleted', $queryBuilder->createNamedParameter(1)),
					$queryBuilder->expr()->eq('tx_camaliga_domain_model_content.hidden', $queryBuilder->createNamedParameter(1))
				)
			]);
		} else if ($my_c==2) {
			$res -> andWhere(...[
				$queryBuilder->expr()->eq('tx_camaliga_domain_model_content.deleted', 0),
				$queryBuilder->expr()->eq('tx_camaliga_domain_model_content.hidden', 0)
			]);
		}
		$res -> andWhere(...[
			$queryBuilder->expr()->orX(
				$queryBuilder->expr()->eq('ref.link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid)),
				$queryBuilder->expr()->like('ref.link', $queryBuilder->createNamedParameter('t3://page?uid=' . $linkto_uid . ' %'))
			)
		]);		
		$result = $res -> orderBy('tx_camaliga_domain_model_content.pid', 'ASC')
		-> addOrderBy('tx_camaliga_domain_model_content.tstamp', 'DESC')
		-> execute();
			
		foreach($result as $row) {
			$uid = $row['uid'];
			$finalArray[$uid] = $row;
		}
		return $finalArray;
	}
	
	/**
	 * Bilder ohne Alt- oder Titel-Text
	 *
	 * @param   integer   Modus
	 * @param   integer   not only in tt_content?
	 *
	 * @return  array     Bilder
	 */
	function getImagesWithout($img_without, $img_other)
	{
		//$pageRep = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
	    $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
		$fileArray = [];
		//$fileOrder = [];
		$referenceArray = [];
		$finalArray = [];
		
		// sys_file: get images
		$table = 'sys_file';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		$statement = $queryBuilder
			->select('*')
			->from($table)
			->where($queryBuilder->expr()->like('mime_type', $queryBuilder->createNamedParameter('image%')))
			->orderBy('name', 'ASC')
			->execute();
		while ($row = $statement->fetch()) {
			$uid = $row['uid'];
			//$fileOrder[] = $uid;
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
				$fileArray[$uid]['meta_uid'] = $row['uid'];
				$fileArray[$uid]['meta_title'] = $row['title'];
				$fileArray[$uid]['meta_alt'] = $row['alternative'];
				$fileArray[$uid]['meta_width'] = $row['width'];
				$fileArray[$uid]['meta_height'] = $row['height'];
			}
		}
		
		// sys_file_reference und tt_content
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference')->createQueryBuilder();
		$res = $queryBuilder ->select(...[
				'sys_file_reference.uid',
				'sys_file_reference.title',
				'sys_file_reference.alternative',
				'sys_file_reference.uid_local',
				'sys_file_reference.uid_foreign',
				'tt_content.pid AS tt_pid',
				'tt_content.colPos AS tt_pos',
				'tt_content.sys_language_uid AS tt_lang'
			]) -> from ('sys_file_reference')
			-> join(
				'sys_file_reference',
				'tt_content',
				'tt_content',
				$queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->quoteIdentifier('tt_content.uid'))
			)
			->where(
				$queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter('tt_content'))
			)
			->orderBy('tt_pid', 'ASC');
		//print_r($queryBuilder->getSQL());
		$result = $res -> execute();
		foreach($result as $row) {
			$uid = $row['uid'];
			$uid_file = $row['uid_local'];
			if ($fileArray[$uid_file]['uid'] == $uid_file) {
				$referenceArray[$uid]['ref_uid'] = $uid;
				$referenceArray[$uid]['ref_title'] = $row['title'];
				$referenceArray[$uid]['ref_alt'] = $row['alternative'];
				$referenceArray[$uid]['tt_uid'] = $referenceArray[$uid]['cid'] = $row['uid_foreign'];
				$referenceArray[$uid]['tt_pid'] = $row['tt_pid'];
				$referenceArray[$uid]['tt_lang'] = $row['tt_lang'];
				$referenceArray[$uid]['tt_pos'] = $row['tt_pos'];
				//$referenceArray[$uid]['file_uid'] = $uid_file;
				$referenceArray[$uid]['file'] = $fileArray[$uid_file];	// file-array
				//echo "uid $uid <br>\n";
			}
		}
		
		if ($img_other) {
			// sys_file_reference ohne tt_content
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference')->createQueryBuilder();
			$res = $queryBuilder ->select(...[
					'uid',
					'title',
					'alternative',
					'uid_local',
					'tablenames'
			]) -> from ('sys_file_reference')
			->where(
				$queryBuilder->expr()->neq('tablenames', $queryBuilder->createNamedParameter('tt_content'))
			)
			->orderBy('uid', 'ASC');
			//print_r($queryBuilder->getSQL());
			$result = $res -> execute();
			foreach($result as $row) {
				$uid = $row['uid'];
				$uid_file = $row['uid_local'];
				if (!$referenceArray[$uid] && ($fileArray[$uid_file]['uid'] == $uid_file)) {
					$referenceArray[$uid]['ref_uid'] = $uid;
					$referenceArray[$uid]['ref_title'] = $row['title'];
					$referenceArray[$uid]['ref_alt'] = $row['alternative'];
					$referenceArray[$uid]['ref_tablenames'] = $row['tablenames'];
					//$referenceArray[$uid]['file_uid'] = $uid_file;
					$referenceArray[$uid]['file'] = $fileArray[$uid_file];	// file-array
					$referenceArray[$uid]['domain'] = '';
					//echo "uid $uid <br>\n";
				}
			}
		}
			
		// Bilder ohne alt oder title
		foreach ($referenceArray as $uid => $refArray) {
			$imgArray = $refArray['file'];
			//echo $imgArray['meta_alt'] .'#'. $imgArray['ref_alt'];
			if (((($img_without == 1) || ($img_without == 3)) && (($imgArray['meta_alt']=='') && ($refArray['ref_alt']==''))) ||
				((($img_without == 2) || ($img_without == 3)) && (($imgArray['meta_title']=='') && ($refArray['ref_title']==''))) ||
				((($img_without == 4) || ($img_without == 6)) && (($imgArray['meta_alt']!='') || ($refArray['ref_alt']!=''))) ||
				((($img_without == 5) || ($img_without == 6)) && (($imgArray['meta_title']!='') || ($refArray['ref_title']!='')))) {
				// neu ab version 1.4.3: final-array enthält reference-Daten statt file-Daten
				if ($refArray['tt_pid']) {
				    $refArray['domain'] = $this->getDomain($refArray['tt_pid'], $refArray['tt_lang']);
				} else {
				    $refArray['domain'] = '';
				}
				$finalArray[] = $refArray;
			}
		}
		return $finalArray;
	}
	
	/**
	 * setAltOrTitle
	 * 
	 * @param	int		$uid			uid of sys_file_reference
	 * @param	string	$alternative	alt-tag
	 * @param	string	$title			title-tag
	 * @return	boolean
	 */
	public function setAltOrTitle($uid, $alternative, $title)
	{
		if ($alternative) {
			$field = 'alternative';
			$value = $alternative;
		} else {
			$field = 'title';
			$value = $title;
		}
		if ($uid && $field) {
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
			$queryBuilder
				->update('sys_file_reference')
				->where(
					$queryBuilder->expr()->eq('uid', $uid)
				)
				->set($field, $value)
				->execute();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get list of pages with RealUrl-path
	 *
	 * @return array
	 */
	public function getPagesRealurl()
	{
		$pages = [];
		$table = 'tx_realurl_pathdata';
		try {
    		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    		$statement = $queryBuilder
    		->select('*')
    		->from($table)
    		->orderBy('page_id', 'ASC')
    		->execute();
    		while ($row = $statement->fetch()) {
    			if (!is_array($pages[$row['page_id']])) {
    				$pages[$row['page_id']] = [];
    			}
    			$pages[$row['page_id']][$row['language_id']] = '/' . $row['pagepath'];
    		}
		} catch (TableNotFoundException $e) {
		    // Die Tabelle fehlt, aber das try hilft auch nicht
		}
		return $pages;
	}
	
	/**
	 * Get list of pages with slug-path
	 * 
	 * @param	int		$hidden			hidden-flag
	 * @return array
	 */
	public function getPagesSlug($hidden)
	{
	    $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
		$pages = [];
		$table = 'pages';
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
		$statement = $queryBuilder
		->select('*')
		->from($table)
		->where($queryBuilder->expr()->eq('deleted', 0))
		->andWhere($queryBuilder->expr()->gt('pid', 0))
		->orderBy('uid', 'ASC')
		->execute();
		while ($row = $statement->fetch()) {
			$p_hidden = $row['hidden'];
			if (($hidden == 0) || (($hidden == 1) && $p_hidden) || (($hidden == 2) && !$p_hidden)) {
				$uid = $row['uid'];
				$sys_language_uid = $row['sys_language_uid'];
				if ($sys_language_uid > 0) {
					$uid = $row['l10n_parent'];
				}
				if (!is_array($pages[$uid])) {
					$pages[$uid] = [];
				}
				$pages[$uid][$sys_language_uid] = [];
				$pages[$uid][$sys_language_uid]['hidden'] = $hidden;
				$pages[$uid][$sys_language_uid]['sys_language_uid'] = $sys_language_uid;
				$pages[$uid][$sys_language_uid]['title'] = $row['title'];
				$pages[$uid][$sys_language_uid]['slug'] = $row['slug'];
				if ($row['slug_locked'] && ($row['slug_locked'] == 1)) {
					$pages[$uid][$sys_language_uid]['slug_locked'] = 1;
				} else {
					$pages[$uid][$sys_language_uid]['slug_locked'] = 0;
				}
				$pages[$uid][$sys_language_uid]['domain'] = $this->getDomain($uid, $sys_language_uid);
			}
		}
		return $pages;
	}
	
	/**
	 * Get the domain + language of a pages-entry
	 *
	 * @param	int		$uid	page-uid
	 * @param	int		$sys_language_uid	language-uid
	 * @return string
	 */
	protected function getDomain($uid, $sys_language_uid = 0) {
	    $domain = '';
	    $rootLineUtility = new \TYPO3\CMS\Core\Utility\RootlineUtility($uid);
	    $rootline = $rootLineUtility->get();
	    $root = array_pop($rootline);
	    if ($root['is_siteroot']) {
	        try {
	            $site = $this->siteFinder->getSiteByPageId($root['uid']);   // oder $uid;
	            $base = $site->getConfiguration()['base'];
	            $lang = $site->getConfiguration()['languages'];
	            $lang = $lang[$sys_language_uid]['base'];
	            $domain = rtrim($base, '/') . rtrim($lang, '/');
	        } catch (SiteNotFoundException $e) {
	            $domain = '';
	        }
	    }
	    return $domain;
	}
	
	/**
	 * addRedirect
	 * 
	 * @param	string	$from	from link
	 * @param	string	$to		to link
	 * @param	int		$regexp	regular expression?
	 * @param	int		$statuscode	Statuscode, e.g. 301
	 * @param	int		$createdby	Created by BE-user
	 */
	public function addRedirect($from, $to, $regexp, $statuscode, $createdby)
	{
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