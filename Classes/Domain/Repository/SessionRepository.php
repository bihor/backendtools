<?php
namespace Fixpunkt\Backendtools\Domain\Repository;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 Kurt Gusbeth <info@quizpalme.de>
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
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('action', $action),
            $query->equals('beuser', $beuser)
        ));
        return $query->execute();
    }


    /**
     * Get list of all used CTypes and list_types
     *
     * @return array
     */
    public function getAllTypes()
    {
        $types = [];
        $types['0#0'] = "Select CType/list_type ...";
        $exclude_ctypes = [
            "html", "text", "image", "textpic", "textmedia", "bullets", "menu",
            "search", "mailform", "indexed_search", "login", "header", "rte",
            "table", "splash", "uploads", "multimedia", "media", "script",
            "shortcut", "div"
        ];
        // Query aufbauen
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->createQueryBuilder();
        $queryBuilder
            ->getRestrictions()
            ->removeAll();
        $res = $queryBuilder ->select(...[
            'CType',
            'list_type'
        ]) -> from ('tt_content');
        $res -> andWhere(...[
            $queryBuilder->expr()->notIn('CType', $queryBuilder->createNamedParameter($exclude_ctypes, Connection::PARAM_STR_ARRAY))
        ]);
        $res -> orderBy('list_type', 'ASC') -> addOrderBy('CType');
        //$res -> groupBy('CType');
        //print_r($res->getSQL());
        $result = $res-> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if ($row['list_type']) {
                $types['1#' . $row['list_type']] = $row['list_type'];
            }
            elseif ($row['CType'] && $row['CType']!='list') {
                $types['2#' . $row['CType']] = $row['CType'];
            }
        }
        return $types;
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
     * @param	int		$my_orderby		order by
     * @param	int		$my_direction	order direction
     * @param	boolen  $gridelements_loaded	Extension gridelements loaded?
     * @return array
     */
    public function getPagesWithExtensions($my_c, $my_p, $my_type, $my_value, $my_flexform, $my_exclude, $my_orderby, $my_direction, $gridelements_loaded)
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
        $more = ($gridelements_loaded) ? 'tt_content.tx_gridelements_backend_layout' : 'tt_content.t3_origuid';

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
            $more,
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
                    $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->gt('pages.starttime', $queryBuilder->createNamedParameter(time())),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->lte('pages.endtime', $queryBuilder->createNamedParameter(time()))
                    )
                )
            ]);
        } else if ($my_p==2) {
            $res -> andWhere(...[
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->lte('pages.starttime', $queryBuilder->createNamedParameter(time())),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.endtime', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(time()))
                )
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
            if ($my_value == 'gridelements_pi1' && $my_type == 2) {
                // wir suchen auch in tx_gridelements_backend_layout
                $res -> andWhere(...[
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('tt_content.pi_flexform', $queryBuilder->createNamedParameter("%" . $queryBuilder->escapeLikeWildcards($my_flexform) . "%")),
                        $queryBuilder->expr()->like('tt_content.tx_gridelements_backend_layout', $queryBuilder->createNamedParameter($queryBuilder->escapeLikeWildcards($my_flexform)))
                    )
                ]);
            } else {
                $res->andWhere(
                    $queryBuilder->expr()->like('tt_content.pi_flexform', $queryBuilder->createNamedParameter("%" . $queryBuilder->escapeLikeWildcards($my_flexform) . "%"))
                );
            }
        }

        $asc = ($my_direction == 1) ? 'DESC' : 'ASC';
        switch ($my_orderby) {
            case 1: $sort = 'tt_content.uid'; break;
            case 2: $sort = 'tt_content.sys_language_uid'; break;
            case 3: $sort = 'tt_content.colPos'; break;
            case 4: $sort = 'tt_content.header'; break;
            case 5: $sort = 'tt_content.CType'; break;
            case 6: $sort = 'tt_content.list_type'; break;
            case 7: $sort = 'pages.title'; break;
            default: $sort = 'tt_content.pid';
        }
        if ($my_orderby == 0) {
            $res -> orderBy($sort, $asc) -> addOrderBy('tt_content.sorting');
        } else {
            $res -> orderBy($sort, $asc) -> addOrderBy('tt_content.pid');
        }
        //print_r($res->getSQL());
        $result = $res-> executeQuery()->fetchAllAssociative();

        //print_r($queryBuilder->getParameters());
        foreach($result as $row) {
            $subject = $row['pi_flexform'];
            if ($subject) {
                $pattern = '/<field index="switchableControllerActions">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
                $matches = array();
                preg_match($pattern, $subject, $matches);
                if (isset($matches[2])) {
                    $row['actions'] = str_replace('###', '&gt;', str_replace(';', ', ', str_replace('&gt;', '###', $matches[2])));
                } else {
                    $pattern = '/<field index="what_to_display">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
                    $matches = array();
                    preg_match($pattern, $subject, $matches);
                    if (isset($matches[2])) {
                        $row['actions'] = $matches[2];
                    } elseif ($row['CType'] == 'wst3bootstrap_fluidrow') {
                        $sections = substr_count($subject,"<section index");
                        if ($sections > 0) {
                            $row['actions'] = $sections . ' cols';
                        }
                    }
                }
            }
            if ($row['sys_language_uid'] > 0) {
                // wir brauchen noch die Übersetzungen aus pages!
                $language_result = $this->getL10n($row['pid'], $row['sys_language_uid']);
                foreach ($language_result as $language_row) {
                    $row['title'] = $language_row['title'];
                    $row['slug'] = $language_row['slug'];
                    $row['pdeleted'] = $language_row['pdeleted'];
                    $row['phidden'] = $language_row['phidden'];
                    $row['pl10n'] = $language_row['pl10n'];
                }
            } else {
                $row['pl10n'] = $row['pid'];
            }
            if ( $row["pdeleted"] ) {
                $row['domain'] = '';
            } else {
                $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
            }
            $row['csvheader'] = str_replace('"', '\'', $row['header']);
            $row['csvtitle'] = str_replace('"', '\'', $row['title']);
            if (isset($row['tx_gridelements_backend_layout'])) {
                $row['misc'] = $row['tx_gridelements_backend_layout'];
                if ($row['misc']=='2cols' || $row['misc']=='3cols' || $row['misc']=='4cols' || $row['misc']=='6cols') {
                    $pattern = '/<field index="xsCol1">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
                    $matches = array();
                    preg_match($pattern, $subject, $matches);
                    if (isset($matches[2])) {
                        $row['misc'] .= ' # xs=' . $matches[2];
                    } else {
                        $pattern = '/<field index="smCol1">([\n|\r|\t| ]*)<value index="vDEF">(.*)</';
                        $matches = array();
                        preg_match($pattern, $subject, $matches);
                        if (isset($matches[2])) {
                            $row['misc'] .= ' # SM=' . $matches[2];
                        }
                    }
                }
            }
            $pages[] = $row;
        }
        return $pages;
    }

    /**
     * Get list of pages/elements modified since a given date
     *
     * @param	int		$my_c			content visibility
     * @param	int		$my_p			pages visibility
     * @param	int		$tstamp		    date as timestamp
     * @return array
     */
    public function getLatestContentElements($my_c, $my_p, $tstamp) {
        $pages = [];
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
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
            'tt_content.tstamp AS ttstamp',
            'pages.title',
            'pages.slug',
            'pages.deleted AS pdeleted',
            'pages.hidden AS phidden',
            'pages.tstamp AS ptstamp'
        ]) -> from ('tt_content')
            -> join(
                'tt_content',
                'pages',
                'pages',
                $queryBuilder->expr()->eq('tt_content.pid', $queryBuilder->quoteIdentifier('pages.uid'))
            )
            ->where($queryBuilder->expr()->gt('tt_content.tstamp', $queryBuilder->createNamedParameter($tstamp)));
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
                    $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->gt('pages.starttime', $queryBuilder->createNamedParameter(time())),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->lte('pages.endtime', $queryBuilder->createNamedParameter(time()))
                    )
                )
            ]);
        } else if ($my_p==2) {
            $res -> andWhere(...[
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->lte('pages.starttime', $queryBuilder->createNamedParameter(time())),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.endtime', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(time()))
                )
            ]);
        }
        $res -> orderBy('tt_content.tstamp', 'DESC');
        //$res -> setMaxResults(10);
        //print_r($res->getSQL());
        $result = $res-> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if ($row['sys_language_uid'] > 0) {
                // wir brauchen noch die Übersetzungen aus pages!
                $language_result = $this->getL10n($row['pid'], $row['sys_language_uid']);
                foreach ($language_result as $language_row) {
                    $row['title'] = $language_row['title'];
                    $row['slug'] = $language_row['slug'];
                    $row['pdeleted'] = $language_row['pdeleted'];
                    $row['phidden'] = $language_row['phidden'];
                    $row['pl10n'] = $language_row['pl10n'];
                }
            } else {
                $row['pl10n'] = $row['pid'];
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
     * Get list of pages with (backend)-layouts
     *
     * @param	int		$my_value		layouts or backend-layouts
     * @param	int		$my_p			pages visibility
     * @return array
     */
    public function getLayouts($my_value, $my_p) {
        $pages = [];
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        // Query aufbauen
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $res = $queryBuilder ->select(...[
            'uid',
            'l10n_parent',
            'sys_language_uid',
            'deleted AS pdeleted',
            'hidden AS phidden',
            'tstamp AS ptstamp',
            'title',
            'slug',
            'layout',
            'backend_layout',
            'backend_layout_next_level'
        ]) -> from ('pages');
        if ($my_value == 0) {
            $res->where(
                $queryBuilder->expr()->gt('layout', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            );
        } else {
            $res->where(
                $queryBuilder->expr()->neq(
                    'backend_layout',
                    $queryBuilder->createNamedParameter('')
                ),
                $queryBuilder->expr()->neq(
                    'backend_layout',
                    $queryBuilder->createNamedParameter('0')
                )
            )
            ->orWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->neq(
                        'backend_layout_next_level',
                        $queryBuilder->createNamedParameter('')
                    ),
                    $queryBuilder->expr()->neq(
                        'backend_layout_next_level',
                        $queryBuilder->createNamedParameter('0')
                    )
                )
            );
        }
        // Restricions
        $queryBuilder
            ->getRestrictions()
            ->removeAll();
        if ($my_p==1) {
            $res -> andWhere(...[
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->gt('pages.starttime', $queryBuilder->createNamedParameter(time())),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->lte('pages.endtime', $queryBuilder->createNamedParameter(time()))
                    )
                )
            ]);
        } else if ($my_p==2) {
            $res -> andWhere(...[
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->lte('pages.starttime', $queryBuilder->createNamedParameter(time())),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.endtime', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(time()))
                )
            ]);
        }
        $res -> orderBy('uid', 'ASC');
        $result = $res-> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if ($row['sys_language_uid'] > 0) {
                $row['pl10n'] = $row['uid'];
                $row['uid'] = $row['l10n_parent'];
            } else {
                $row['pl10n'] = $row['uid'];
            }
            $row['pid'] = $row['uid'];
            $row['uid'] = 0;
            if ( $row["pdeleted"] ) {
                $row['domain'] = '';
            } else {
                $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
            }
            $row['csvheader'] = '';
            $row['csvtitle'] = str_replace('"', '\'', $row['title']);
            $pages[] = $row;
        }
        return $pages;
    }

    /**
     * Get list of pages modified since a given date
     *
     * @param	int		$my_p			pages visibility
     * @param	int		$tstamp		    date as timestamp
     * @return array
     */
    public function getLatestPages($my_p, $tstamp) {
        $pages = [];
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        // Query aufbauen
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $res = $queryBuilder ->select(...[
            'uid',
            'l10n_parent',
            'title',
            'slug',
            'sys_language_uid',
            'deleted AS pdeleted',
            'hidden AS phidden',
            'tstamp AS ptstamp'
        ]) -> from ('pages')
            ->where($queryBuilder->expr()->gt('tstamp', $queryBuilder->createNamedParameter($tstamp)));
        // Restricions
        $queryBuilder
            ->getRestrictions()
            ->removeAll();
        if ($my_p==1) {
            $res -> andWhere(...[
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->gt('pages.starttime', $queryBuilder->createNamedParameter(time())),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->lte('pages.endtime', $queryBuilder->createNamedParameter(time()))
                    )
                )
            ]);
        } else if ($my_p==2) {
            $res -> andWhere(...[
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->lte('pages.starttime', $queryBuilder->createNamedParameter(time())),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.endtime', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(time()))
                )
            ]);
        }
        $res -> orderBy('tstamp', 'DESC');
        //$res -> setMaxResults(10);
        //print_r($res->getSQL());
        $result = $res-> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if ($row['sys_language_uid'] > 0) {
                $row['pl10n'] = $row['uid'];
                $row['uid'] = $row['l10n_parent'];
            } else {
                $row['pl10n'] = $row['uid'];
            }
            $row['pid'] = $row['uid'];
            $row['uid'] = 0;
            if ( $row["pdeleted"] ) {
                $row['domain'] = '';
            } else {
                $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
            }
            $row['csvheader'] = '';
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
        $result = $res -> executeQuery()->fetchAllAssociative();
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
                    $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(1)),
                    $queryBuilder->expr()->gt('pages.starttime', $queryBuilder->createNamedParameter(time())),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->lte('pages.endtime', $queryBuilder->createNamedParameter(time()))
                    )
                )
            ]);
        } else if ($my_p==2) {
            $res -> andWhere(...[
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('pages.hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->lte('pages.starttime', $queryBuilder->createNamedParameter(time())),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('pages.endtime', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->gt('pages.endtime', $queryBuilder->createNamedParameter(time()))
                )
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
        //print_r($res->getSQL());
        $result = $res -> orderBy('tt_content.pid')
            -> addOrderBy('tt_content.sorting')
            -> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if ( $row["pdeleted"] ) {
                $row['domain'] = '';
            } else {
                $row['domain'] = $this->getDomain($row['pid'], $row['sys_language_uid']);
            }
            if ($row['sys_language_uid'] > 0) {
                // wir brauchen noch die Übersetzungen aus pages!
                $language_result = $this->getL10n($row['pid'], $row['sys_language_uid']);
                foreach ($language_result as $language_row) {
                    $row['title'] = $language_row['title'];
                    $row['slug'] = $language_row['slug'];
                    $row['pdeleted'] = $language_row['pdeleted'];
                    $row['phidden'] = $language_row['phidden'];
                    $row['pl10n'] = $language_row['pl10n'];
                }
            } else {
                $row['pl10n'] = $row['pid'];
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
            -> executeQuery()->fetchAllAssociative();

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
            -> executeQuery()->fetchAllAssociative();

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
            -> executeQuery()->fetchAllAssociative();

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
            -> executeQuery()->fetchAllAssociative();

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
            -> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            $uid = $row['uid'];
            $finalArray[$uid] = $row;
        }
        return $finalArray;
    }

    /**
     * Bilder die fehlen
     *
     * @param   integer   not only in tt_content?
     *
     * @return  array     Bilder
     */
    function getMissingImages($img_other)
    {
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $fileArray = [];
        $referenceArray = [];
        $finalArray = [];

        // sys_file: get images
        $table = 'sys_file';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('missing', 1))
            ->orderBy('name', 'ASC')
            ->executeQuery()->fetchAllAssociative();
      //  ->where($queryBuilder->expr()->like('mime_type', $queryBuilder->createNamedParameter('image%')))

        foreach($result as $row) {
            $uid = $row['uid'];
            $fileArray[$uid] = $row;
            $fileArray[$uid]['used'] = false;
        }

        // sys_file_metadata
        $table = 'sys_file_metadata';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if (isset($row['file'])) {
                $uid = $row['file'];
                if (isset($fileArray[$uid]) && isset($fileArray[$uid]['uid']) && ($fileArray[$uid]['uid'] == $uid)) {
                    $fileArray[$uid]['meta_uid'] = $row['uid'];
                    $fileArray[$uid]['meta_title'] = $row['title'];
                    $fileArray[$uid]['meta_alt'] = $row['alternative'];
                    $fileArray[$uid]['meta_width'] = $row['width'];
                    $fileArray[$uid]['meta_height'] = $row['height'];
                }
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
        $result = $res -> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            $uid = $row['uid'];
            $uid_file = $row['uid_local'];
            if (isset($fileArray[$uid_file]) && isset($fileArray[$uid_file]['uid']) && ($fileArray[$uid_file]['uid'] == $uid_file)) {
                $referenceArray[$uid] = [];
                $referenceArray[$uid]['ref_uid'] = $uid;
                $referenceArray[$uid]['ref_title'] = $row['title'];
                $referenceArray[$uid]['ref_alt'] = $row['alternative'];
                $referenceArray[$uid]['tt_uid'] = $referenceArray[$uid]['cid'] = $row['uid_foreign'];
                $referenceArray[$uid]['tt_pid'] = $row['tt_pid'];
                $referenceArray[$uid]['tt_lang'] = $row['tt_lang'];
                $referenceArray[$uid]['tt_pos'] = $row['tt_pos'];
                //$referenceArray[$uid]['file_uid'] = $uid_file;
                $referenceArray[$uid]['file'] = $fileArray[$uid_file];	// file-array
                $fileArray[$uid_file]['used'] = true;
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
            $result = $res -> executeQuery()->fetchAllAssociative();

            foreach($result as $row) {
                $uid = $row['uid'];
                $uid_file = $row['uid_local'];
                if (isset($fileArray[$uid_file]) && isset($fileArray[$uid_file]['uid']) && !isset($referenceArray[$uid]) && ($fileArray[$uid_file]['uid'] == $uid_file)) {
                    $referenceArray[$uid] = [];
                    $referenceArray[$uid]['ref_uid'] = $uid;
                    $referenceArray[$uid]['ref_title'] = $row['title'];
                    $referenceArray[$uid]['ref_alt'] = $row['alternative'];
                    $referenceArray[$uid]['ref_tablenames'] = $row['tablenames'];
                    //$referenceArray[$uid]['file_uid'] = $uid_file;
                    $referenceArray[$uid]['file'] = $fileArray[$uid_file];	// file-array
                    $referenceArray[$uid]['domain'] = '';
                    $fileArray[$uid_file]['used'] = true;
                    //echo "uid $uid <br>\n";
                }
            }
        }

        // Bilder mit Domain
        $i = 0;
        foreach ($referenceArray as $uid => $refArray) {
            if (isset($refArray['tt_pid'])) {
                $refArray['domain'] = $this->getDomain($refArray['tt_pid'], $refArray['tt_lang']);
            } else {
                $refArray['domain'] = '';
            }
            $finalArray[$i] = $refArray;
            $i++;
        }
        $doubleArray = [];
        $doubleArray[0] = $fileArray;
        $doubleArray[1] = $finalArray;
        return $doubleArray;
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
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->like('mime_type', $queryBuilder->createNamedParameter('image%')))
            ->orderBy('name', 'ASC')
            ->executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            $uid = $row['uid'];
            //$fileOrder[] = $uid;
            $fileArray[$uid] = $row;
        }

        // sys_file_metadata
        $table = 'sys_file_metadata';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            if (isset($row['file'])) {
                $uid = $row['file'];
                if (isset($fileArray[$uid]) && isset($fileArray[$uid]['uid']) && ($fileArray[$uid]['uid'] == $uid)) {
                    $fileArray[$uid]['meta_uid'] = $row['uid'];
                    $fileArray[$uid]['meta_title'] = $row['title'];
                    $fileArray[$uid]['meta_alt'] = $row['alternative'];
                    $fileArray[$uid]['meta_width'] = $row['width'];
                    $fileArray[$uid]['meta_height'] = $row['height'];
                }
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
        $result = $res -> executeQuery()->fetchAllAssociative();

        foreach($result as $row) {
            $uid = $row['uid'];
            $uid_file = $row['uid_local'];
            if (isset($fileArray[$uid_file]) && isset($fileArray[$uid_file]['uid']) && ($fileArray[$uid_file]['uid'] == $uid_file)) {
                $referenceArray[$uid] = [];
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
            $result = $res -> executeQuery()->fetchAllAssociative();

            foreach($result as $row) {
                $uid = $row['uid'];
                $uid_file = $row['uid_local'];
                if (isset($fileArray[$uid_file]) && isset($fileArray[$uid_file]['uid']) && !isset($referenceArray[$uid]) && ($fileArray[$uid_file]['uid'] == $uid_file)) {
                    $referenceArray[$uid] = [];
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
            if (((($img_without == 1) || ($img_without == 3)) &&
                    ((!isset($imgArray['meta_alt']) || $imgArray['meta_alt']=='') && (!isset($refArray['ref_alt']) || $refArray['ref_alt']==''))) ||
                ((($img_without == 2) || ($img_without == 3)) &&
                    ((!isset($imgArray['meta_title']) || $imgArray['meta_title']=='') && (!isset($refArray['ref_title']) || $refArray['ref_title']==''))) ||
                ((($img_without == 4) || ($img_without == 6)) &&
                    ((isset($imgArray['meta_alt']) && $imgArray['meta_alt']!='') || (isset($refArray['ref_alt']) && $refArray['ref_alt']!=''))) ||
                ((($img_without == 5) || ($img_without == 6)) &&
                    ((isset($imgArray['meta_title']) && $imgArray['meta_title']!='') || (isset($refArray['ref_title']) && $refArray['ref_title']!='')))) {
                // neu ab version 1.4.3: final-array enthält reference-Daten statt file-Daten
                if (isset($refArray['tt_pid'])) {
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
                ->executeStatement();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a translation to a page-record
     *
     * @param	int		$parent	page-uid
     * @param	int		$sys_language_uid	language-uid
     * @return array
     */
    function getL10n($parent, $sys_language_uid)
    {
        $queryBuilderPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $language_res = $queryBuilderPages ->select(...[
            'pages.uid AS pl10n',
            'pages.title',
            'pages.slug',
            'pages.deleted AS pdeleted',
            'pages.hidden AS phidden'
        ]) -> from ('pages');
        $queryBuilderPages
            ->getRestrictions()
            ->removeAll();
        $language_res -> andWhere(...[
            $queryBuilderPages->expr()->eq('l10n_parent', $queryBuilderPages->createNamedParameter($parent, \PDO::PARAM_INT)),
            $queryBuilderPages->expr()->eq('sys_language_uid', $queryBuilderPages->createNamedParameter($sys_language_uid, \PDO::PARAM_INT))
        ]);
        return $language_res-> executeQuery()->fetchAllAssociative();
    }

    /**
     * Get the domain + extra-path + language of a pages-entry
     *
     * @param	int		$uid	page-uid
     * @param	int		$sys_language_uid	language-uid
     * @return string
     */
    protected function getDomain($uid, $sys_language_uid = 0)
    {
        $domain = '';
        if ($sys_language_uid == -1) {
            $sys_language_uid = 0;
        }
        $rootLineUtility = new \TYPO3\CMS\Core\Utility\RootlineUtility($uid);
        try {
            $rootline = $rootLineUtility->get();
            $root = array_pop($rootline);
        } catch (\Exception $e) {
            return '';
        }
        if (isset($root['is_siteroot'])) {
            try {
                $site = $this->siteFinder->getSiteByPageId($root['uid']);   // oder $uid;
                $base = $site->getConfiguration()['base'];
                $lang = $site->getConfiguration()['languages'];
                $lang = $lang[$sys_language_uid]['base'];
                if ((substr($base, 0, 4) == 'http') && (substr($lang, 0, 4) == 'http')) {
                    // wenn die Domain beides mal benutzt wird, entfernen wir sie bei der Sprache
                    $parse_url = parse_url($lang);
                    $lang = $parse_url['path'];
                }
                $domain = rtrim($base, '/') . rtrim($lang, '/');
                if ((substr($base, 0, 4) != 'http') && (strlen($base) > 4)) {
                    if (substr($base, 0, 2) == '//') {
                        // muss nicht sein:   $domain = 'http:' . $domain;
                    } else if (substr($base, 0, 1) == '/') {
                        $domain = 'http:/' . $domain;
                    } else {
                        $domain = 'http://' . $domain;
                    }
                }
            } catch (\Exception $e) {
                return '';
            }
        }
        return $domain;
    }


    /**
     * Take only pages under a pid
     *
     * @param	array	$pages	page-array
     * @param	int		$uid	page-uid
     * @return array
     */
    public function filterPagesRecursive($pages, $uid)
    {
        $tempPages = [];
        foreach ($pages as $page) {
            $pid = 0;
            if (isset($page['pid'])) {
                $pid = $page['pid'];
            }
            if (!$pid && isset($page['tt_pid'])) {
                $pid = $page['tt_pid'];
            }
            if ($this->isInRootLine($pid, $uid)) {
                $tempPages[] = $page;
            }
        }
        return $tempPages;
    }

    /**
     * Check if a page is in the rootline
     *
     * @param	int		$uid	page-uid
     * @param	int		$searchUid	page-uid which should be in the rootline
     * @return boolean
     */
    public function isInRootLine($uid, $searchUid)
    {
        $rootLineUtility = new \TYPO3\CMS\Core\Utility\RootlineUtility($uid);
        $rootline = $rootLineUtility->get();
        foreach ($rootline as $page) {
            if ($page['uid'] == $searchUid) {
                return true;
            }
        }
        return false;
    }


    /**
     * Get all domains
     *
     * @return array
     */
    public function getAllDomains()
    {
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $this->siteFinder->getAllSites();
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
                'source_host' => '*',
                'source_path' => $from,
                'target' => $to,
                'is_regexp' => $regexp,
                'target_statuscode' => $statuscode,
                'updatedon' => time(),
                'createdon' => time()
            ])
            ->executeStatement();
       // 'createdby' => intval($createdby), klappt nicht mehr in TYPO3 12!
    }

    /**
     * deleteRedirect
     *
     * @param	int		$uid	uid
     */
    public function deleteRedirect($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_redirect');
        return $queryBuilder
            ->delete('sys_redirect')
            ->where(
                $queryBuilder->expr()->eq('uid', (int) $uid)
            )
            ->executeStatement();
    }

    /**
     * getRedirects
     *
     * @return array
     */
    public function getRedirects()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_redirect');
        return $queryBuilder->select(...[
            'uid',
            'source_host',
            'source_path',
            'target'
        ]) -> from ('sys_redirect')
            ->executeQuery()->fetchAllAssociative();
    }

    /**
     * Fetches the record with the given UID from the given table.
     *
     * The filter option accepts two values:
     *
     * "disabled" will filter out disabled and deleted records.
     * "deleted" filters out deleted records but will return disabled records.
     * If nothing is specified all records will be returned (including deleted).
     *
     * @param string $tableName The name of the table from which the record should be fetched.
     * @param int $uid The UID of the record that should be fetched.
     * @param string $filter A filter setting, can be empty or "disabled" or "deleted".
     * @return array|bool The result row as associative array or false if nothing is found.
     */
    public function getRecordRow($tableName, $uid, $filter = '')
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);

        switch ($filter) {
            case 'disabled':
                // All default restrictions for the QueryBuilder stay active
                break;
            case 'deleted':
                $queryBuilder->getRestrictions()
                    ->removeAll()
                    ->add(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction::class));
                break;
            default:
                $queryBuilder->getRestrictions()->removeAll();
        }

        return $queryBuilder
            ->select('*')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->executeQuery()->fetchAssociative();
    }
}