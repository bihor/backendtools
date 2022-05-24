<?php
namespace Fixpunkt\Backendtools\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2021 Kurt Gusbeth <k.gusbeth@fixpunkt.com>, fixpunkt werbeagentur gmbh
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
 * SessionController
 */
class SessionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * contentRepository
     *
     * @var \Fixpunkt\Backendtools\Domain\Repository\SessionRepository
     */
    protected $sessionRepository;

    /**
     * Injects the session-Repository
     *
     * @param \Fixpunkt\Backendtools\Domain\Repository\SessionRepository $sessionRepository
     */
    public function injectSessionRepository(\Fixpunkt\Backendtools\Domain\Repository\SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('list', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('list');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue3(0);
            $default->setValue4('');
            $default->setValue5('');
            $default->setValue6('');
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('my_c')) {
            $my_c = intval($this->request->getArgument('my_c'));		// content visibility
            $default->setValue1($my_c);
        } else $my_c = $default->getValue1();
        if ($this->request->hasArgument('my_p')) {
            $my_p = intval($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else $my_p = $default->getValue2();
        if ($this->request->hasArgument('my_type')) {
            $my_type = intval($this->request->getArgument('my_type'));	// type
            $default->setValue3($my_type);
        } else $my_type = $default->getValue3();
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// type value
            $default->setValue4($my_value);
        } else $my_value = $default->getValue4();
        if ($this->request->hasArgument('my_flexform')) {
            $my_flexform = $this->request->getArgument('my_flexform');	// flexform value
            $default->setValue5($my_flexform);
        } else $my_flexform = $default->getValue5();
        if ($this->request->hasArgument('my_exclude')) {
            $my_exclude = $this->request->getArgument('my_exclude');	// exclude type
            $default->setValue6($my_exclude);
        } else $my_exclude = $default->getValue6();
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        if ($this->request->hasArgument('my_outp')) {
            $my_outp = intval($this->request->getArgument('my_outp'));		// output
        } else $my_outp = 0;
        if ($this->request->hasArgument('my_orderby')) {
            $my_orderby = intval($this->request->getArgument('my_orderby'));		// order by
        } else $my_orderby = 0;
        if ($this->request->hasArgument('my_direction')) {
            $my_direction = intval($this->request->getArgument('my_direction'));		// order direction
        } else $my_direction = 0;
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = intval($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else $my_recursive = $default->getPagestart();

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $gridelements_loaded = ExtensionManagementUtility::isLoaded('gridelements');
        $pages = $this->sessionRepository->getPagesWithExtensions(
            $my_c, $my_p, $my_type, $my_value, $my_flexform, $my_exclude, $my_orderby, $my_direction, $gridelements_loaded
        );
        $types = $this->sessionRepository->getAllTypes();
        if ($my_recursive > 0) {
            $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
        }

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->view->assign('my_p', $my_p);
        $this->view->assign('my_c', $my_c);
        $this->view->assign('my_type', $my_type);
        $this->view->assign('my_value', $my_value);
        $this->view->assign('my_exclude', $my_exclude);
        $this->view->assign('my_flexform', $my_flexform);
        $this->view->assign('my_page', $my_page);
        $this->view->assign('my_outp', $my_outp);
        $this->view->assign('my_recursive', $my_recursive);
        $this->view->assign('my_orderby', $my_orderby);
        $this->view->assign('my_direction', $my_direction);
        $this->view->assign('rows', count($pages));
        $this->view->assign('pages', $pages);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('types', $types);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'list');
    }

    /**
     * action latest
     *
     * @return void
     */
    public function latestAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('latest', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('latest');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue4('01.01.' . date('Y') . ' 00:00:00');
            $default->setValue5('CET');
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('my_c')) {
            $my_c = intval($this->request->getArgument('my_c'));		// content visibility
            $default->setValue1($my_c);
        } else $my_c = $default->getValue1();
        if ($this->request->hasArgument('my_p')) {
            $my_p = intval($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else $my_p = $default->getValue2();
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// date and time
            $default->setValue4($my_value);
        } else $my_value = $default->getValue4();
        if ($this->request->hasArgument('my_zone')) {
            $my_zone = $this->request->getArgument('my_zone');		    // date zone
            $default->setValue5($my_zone);
        } else $my_zone = $default->getValue5();
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        if ($this->request->hasArgument('my_outp')) {
            $my_outp = intval($this->request->getArgument('my_outp'));		// output
        } else $my_outp = 0;
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = intval($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else $my_recursive = $default->getPagestart();

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if (!$my_zone) {
            $my_zone = 'CET';
        }
        $d = \DateTime::createFromFormat(
            'd.m.Y H:i:s',
            $my_value,
            new \DateTimeZone($my_zone)
        );
        if ($d === false) {
            $tstamp = time();
        } else {
            $tstamp = $d->getTimestamp();
        }

        $pages = $this->sessionRepository->getLatestContentElements(
            $my_c, $my_p, $tstamp
        );
        $pages2 = $this->sessionRepository->getLatestPages(
            $my_p, $tstamp
        );
        foreach ($pages2 as $page) {
            $pid = $page['pid'];
            $sys_lang_uid = $page['sys_language_uid'];
            $found = false;
            foreach ($pages as $ce) {
                if (($ce['pid'] == $pid) && ($ce['sys_language_uid'] == $sys_lang_uid)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $pages[] = $page;
            }
        }
        if ($my_recursive > 0) {
            $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
        }
        foreach ($pages as $key => $page) {
            if (isset($page['ttstamp'])) {
                if ($page['ptstamp'] > $page['ttstamp']) {
                    $pages[$key]['sorting'] = $page['ptstamp'];
                } else {
                    $pages[$key]['sorting'] = $page['ttstamp'];
                }
            } else {
                $pages[$key]['sorting'] = $page['ptstamp'];
            }
        }
        usort($pages, function($a, $b) {
            return $b['sorting'] <=> $a['sorting'];
        });

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->view->assign('my_p', $my_p);
        $this->view->assign('my_c', $my_c);
        $this->view->assign('my_value', $my_value);
        $this->view->assign('my_zone', $my_zone);
        $this->view->assign('my_page', $my_page);
        $this->view->assign('my_outp', $my_outp);
        $this->view->assign('my_recursive', $my_recursive);
        $this->view->assign('rows', count($pages));
        $this->view->assign('pages', $pages);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'latest');
    }

    /**
     * action backend layouts
     *
     * @return void
     */
    public function layoutsAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('layouts', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('layouts');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// date and time
            $default->setValue1($my_value);
        } else $my_value = $default->getValue1();
        if ($this->request->hasArgument('my_p')) {
            $my_p = intval($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else $my_p = $default->getValue2();
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        if ($this->request->hasArgument('my_outp')) {
            $my_outp = intval($this->request->getArgument('my_outp'));		// output
        } else $my_outp = 0;
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = intval($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else $my_recursive = $default->getPagestart();

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $pages = $this->sessionRepository->getLayouts( $my_value, $my_p );
        if ($my_recursive > 0) {
            $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
        }

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->view->assign('my_p', $my_p);
        $this->view->assign('my_value', $my_value);
        $this->view->assign('my_page', $my_page);
        $this->view->assign('my_outp', $my_outp);
        $this->view->assign('my_recursive', $my_recursive);
        $this->view->assign('rows', count($pages));
        $this->view->assign('pages', $pages);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'layouts');
    }

    /**
     * action filedeletion
     *
     * @return void
     */
    public function filedeletionAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('filedeletion', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('filedeletion');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue3(0);
            $default->setValue4('');
            $default->setValue5('0');
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('method')) {
            $method = intval($this->request->getArgument('method'));
            $default->setValue1($method);
        } else $method = $default->getValue1();
        if ($this->request->hasArgument('bytes')) {
            $bytes = intval($this->request->getArgument('bytes'));
            $default->setValue2($bytes);
        } else $bytes = $default->getValue2();
        if ($this->request->hasArgument('convert')) {
            $convert = $this->request->getArgument('convert');
            $default->setValue5($convert);
        } else $convert = $default->getValue5();
        if ($this->request->hasArgument('delfile')) {
            $delfile = $this->request->getArgument('delfile');
            //	$default->setValue4($delfile);
        } else $delfile = ''; // $default->getValue4();


        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $groesse = 0;
        $groesse_total = 0;
        $content = '';

        if ($delfile) {
            $total=0;
            $success=0;
            $filename = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . 'fileadmin/' . $delfile;
            if (is_file($filename) && file_exists($filename)) {
                if (!$method) $content .= "This is the file content:<br />\n";
                $filecontent = fopen($filename,"r");
                while (!feof($filecontent)) {
                    $row = trim(fgets($filecontent));
                    if ($convert == 'iso') $row = utf8_decode ( $row );
                    if ($convert == 'utf8') $row = utf8_encode ( $row );
                    if (is_file($row) && file_exists($row)) {
                        if ($bytes) {
                            $groesse = filesize($row);
                            $groesse_total += $groesse;
                        }
                        if ($method && strpos($row, '/uploads/')>0) {
                            if (unlink($row)) $success++;
                            else $content .= "$row could not be deleted!<br />\n";
                        } else {
                            $content .= ($bytes) ? "$row ($groesse bytes)<br />\n" : "$row<br />\n";
                        }
                    } else {
                        $content .= "$row not found!<br />\n";
                    }
                    $total++;
                }
                fclose ($filecontent);
                if ($bytes) $content .= "<br />That are $groesse_total bytes (".$this->formatBytes($groesse_total).").";
                $content .= "<br />$success/$total files deleted.";
            } else {
                $content .= 'Note: file not found!!!';
            }
        }
        $this->view->assign('method', $method);
        $this->view->assign('bytes', $bytes);
        $this->view->assign('convert', $convert);
        $this->view->assign('delfile', $delfile);
        $this->view->assign('message', $content);
    }

    /**
     * action images: images without alt- or title-tag
     *
     * @return void
     */
    public function imagesAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('images', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('images');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('img_without')) {
            $img_without = intval($this->request->getArgument('img_without'));
            $default->setValue1($img_without);
        } else $img_without = $default->getValue1();
        if ($this->request->hasArgument('img_other')) {
            $img_other = intval($this->request->getArgument('img_other'));
            $default->setValue2($img_other);
        } else $img_other = $default->getValue2();
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = intval($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else $my_recursive = $default->getPagestart();

        if ($img_without) {
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        } else {
            $finalArray = [];
        }
        $replacedArray = [];

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $count=0;

        if (($img_without == 1) && $this->request->hasArgument('replace_empty_alt')) {
            // alt-Tags setzen. In der sys_file_reference
            foreach ($finalArray as $key => $refArray) {
                $uid = $refArray['ref_uid'];
                $imgArray = $refArray['file'];
                if ($refArray['ref_title'])
                    $finalArray[$key]['ref_alt'] = $refArray['ref_title'];
                else if ($imgArray['meta_title'])
                    $finalArray[$key]['ref_alt'] = $imgArray['meta_title'];
                else {
                    if (strrpos($imgArray['name'], '.') > 0)
                        $finalArray[$key]['ref_alt'] = trim(str_replace('_', ' ', substr($imgArray['name'], 0, strrpos($imgArray['name'], '.'))));
                    else
                        $finalArray[$key]['ref_alt'] = trim(str_replace('_', ' ', $imgArray['name']));
                }
                $success = $this->sessionRepository->setAltOrTitle($uid, $finalArray[$key]['ref_alt'], '');
                if ($success) {
                    $count++;
                    $replacedArray[] = $finalArray[$key];
                }
            }
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        } else if (($img_without == 2) && $this->request->hasArgument('replace_empty_meta')) {
            // title-Tags setzen. In der sys_file_reference
            foreach ($finalArray as $key => $refArray) {
                $uid = $refArray['ref_uid'];
                $imgArray = $refArray['file'];
                if ($refArray['ref_alt'])
                    $finalArray[$key]['ref_title'] = $refArray['ref_alt'];
                else if ($imgArray['meta_alt'])
                    $finalArray[$key]['ref_title'] = $imgArray['meta_alt'];
                else {
                    if (strrpos($imgArray['name'], '.') > 0)
                        $finalArray[$key]['ref_title'] = trim(str_replace('_', ' ', substr($imgArray['name'], 0, strrpos($imgArray['name'], '.'))));
                    else
                        $finalArray[$key]['ref_title'] = trim(str_replace('_', ' ', $imgArray['name']));
                }
                $success = $this->sessionRepository->setAltOrTitle($uid, '', $finalArray[$key]['ref_title']);
                if ($success) {
                    $count++;
                    $replacedArray[] = $finalArray[$key];
                }
            }
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        }
        if (count($finalArray)>0 && $my_recursive>0) {
            $finalArray = $this->sessionRepository->filterPagesRecursive($finalArray, $my_recursive);
        }

        if (!$finalArray) $finalArray = [];
        $arrayPaginator = new ArrayPaginator($finalArray, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->view->assign('img_without', $img_without);
        $this->view->assign('img_other', $img_other);
        $this->view->assign('count', $count);
        $this->view->assign('images', $finalArray);
        $this->view->assign('imagesReplaced', $replacedArray);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('my_page', $my_page);
        $this->view->assign('my_recursive', $my_recursive);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'images');
    }

    /**
     * action pagesearch: find pages which are linked
     *
     * @return void
     */
    public function pagesearchAction()
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('pagesearch', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('pagesearch');
            $default->setValue1(0);
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('my_c')) {
            $my_c = intval($this->request->getArgument('my_c'));
            $default->setValue1($my_c);
        } else $my_c = $default->getValue1();
        if ($this->request->hasArgument('my_p')) {
            $my_p = intval($this->request->getArgument('my_p'));
            $default->setValue2($my_p);
        } else $my_p = $default->getValue2();
        if ($this->request->hasArgument('exttoo')) {
            $exttoo = intval($this->request->getArgument('exttoo'));
            $default->setValue3($exttoo);
        } else $exttoo = $default->getValue3();
        if ($this->request->hasArgument('linksto')) {
            $linksto = $this->request->getArgument('linksto');
            $default->setValue4($linksto);
        } else $linksto = $default->getValue4();
        $linkto_uid = intval($linksto);
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = intval($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else $my_recursive = $default->getPagestart();

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $pages = [];
        $news = [];
        $camaliga = [];
        if ($linkto_uid > 0) {
            $pages = $this->sessionRepository->getPageLinks($my_c, $my_p, $linkto_uid);
            if ($exttoo) {
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {
                    $news = $this->sessionRepository->getNewsLinks($my_c, $my_p, $linkto_uid);
                }
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('camaliga')) {
                    $camaliga = $this->sessionRepository->getCamaligaLinks($my_c, $my_p, $linkto_uid);
                }
            }
            if ($my_recursive > 0) {
                $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
            }
        }

        if (!$pages) $pages = [];
        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->view->assign('my_c', $my_c);
        $this->view->assign('my_p', $my_p);
        $this->view->assign('linksto', $linksto);
        $this->view->assign('exttoo', $exttoo);
        $this->view->assign('pages', $pages);
        $this->view->assign('news', $news);
        $this->view->assign('camaliga', $camaliga);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('my_page', $my_page);
        $this->view->assign('my_recursive', $my_recursive);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'pagesearch');
    }

    /**
     * action redirects import
     *
     * @return void
     */
    public function redirectsAction()
    {
        $content = '';
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('redirects', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('redirects');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue3(0);
            $default->setValue4('');
            $default->setValue5('0');
            $default->setValue6('301');
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('method')) {
            $method = intval($this->request->getArgument('method'));
            $default->setValue1($method);
        } else $method = $default->getValue1();
        if ($this->request->hasArgument('regex')) {
            $regex = intval($this->request->getArgument('regex'));
            $default->setValue2($regex);
        } else $regex = $default->getValue2();
        if ($this->request->hasArgument('convert')) {
            $convert = $this->request->getArgument('convert');
            $default->setValue5($convert);
        } else $convert = $default->getValue5();
        if ($this->request->hasArgument('defaultstatuscode')) {
            $defaultstatuscode = $this->request->getArgument('defaultstatuscode');
            $default->setValue6($defaultstatuscode);
        } else $defaultstatuscode = $default->getValue6();
        if ($this->request->hasArgument('impfile')) {
            $impfile = $this->request->getArgument('impfile');
        } else $impfile = '';


        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if ($impfile) {
            $total=0;
            $success=0;
            $regexp = ($regex) ? 1 : 0;
            $treffer = [];
            $rewrites = [];
            $filename = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . 'fileadmin/' . $impfile;
            if (is_file($filename) && file_exists($filename)) {
                $content .= "This is the result of the file content:<br /><table>\n";
                $filecontent = fopen($filename,"r");
                while (!feof($filecontent)) {
                    $row = trim(fgets($filecontent));
                    if ($convert == 'iso') $row = utf8_decode ( $row );
                    if ($convert == 'utf8') $row = utf8_encode ( $row );
                    $row = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($row));	// tab und/oder mehrere Spaces zu einem Space umwandeln
                    $rewrites = explode(' ', $row);
                    preg_match('/R=(\d+)/', $rewrites[3], $treffer);
                    $statuscode = $treffer[1];
                    if (!$statuscode) {
                        $statuscode = intval($defaultstatuscode);
                    }
                    if ($rewrites[1] && (substr($rewrites[1], 0, 2) != '^/') && (substr($rewrites[1], 0, 1) == '^')) {
                        if ($regexp) {
                            $rewrites[1] = '^/' . substr($rewrites[1], 1);    // aus ^xyz wird ^/xyz
                        } else {
                            $rewrites[1] = '/' . substr($rewrites[1], 1);    // aus ^xyz wird /xyz
                        }
                    }
                    if ($regexp) {
                        $rewrites[1] = '#' . $rewrites[1] . '#';        // TYPO3 will das so
                    }
                    if ($rewrites[1] && $rewrites[2] && (strlen($rewrites[1])>2)) {
                        if ($method) {
                            if ($this->sessionRepository->addRedirect($rewrites[1], $rewrites[2], $regexp, $statuscode, $beuser_id)) {
                                $content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#00ff00;"> to </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
                                $success++;
                            } else {
                                $content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#ff0000;"> did not worked </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
                            }
                        } else {
                            $content .= '<tr><td>' . $rewrites[1] . '</td><td> to </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
                            $success++;
                        }
                    } else {
                        $content .= '<tr><td>' . $rewrites[1] . '</td><td style="color:#ff0000;"> ignored </td><td>' . $rewrites[2] . '</td><td>' . $statuscode . "</td></tr>\n";
                    }
                    $total++;
                }
                fclose ($filecontent);
                $content .= "</table><br />$success/$total lines ";
                $content .= ($method) ? 'added.' : 'accepted.';
            } else {
                $content .= 'Note: file not found!!!';
            }
        }
        $this->view->assign('method', $method);
        $this->view->assign('regex', $regex);
        $this->view->assign('defaultstatuscode', $defaultstatuscode);
        $this->view->assign('convert', $convert);
        $this->view->assign('impfile', $impfile);
        $this->view->assign('message', $content);
    }

    /**
     * action redirects check
     *
     * @return void
     */
    public function redirectscheckAction()
    {
        $content = '';
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('redirectscheck', $beuser_id);
        if ($result->count() == 0) {
            $new = TRUE;
            $default = GeneralUtility::makeInstance('Fixpunkt\\Backendtools\\Domain\\Model\\Session');
            $default->setAction('redirectscheck');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = FALSE;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = intval($this->request->getArgument('currentPage'));
        } else $currentPage = 1;
        if ($this->request->hasArgument('my_http')) {
            $my_http = intval($this->request->getArgument('my_http'));
            $default->setValue1($my_http);
        } else $my_http = $default->getValue1();
        if ($this->request->hasArgument('my_error')) {
            $my_error = intval($this->request->getArgument('my_error'));
            $default->setValue2($my_error);
        } else $my_error = $default->getValue2();
        if ($this->request->hasArgument('my_page')) {
            $my_page = intval($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else $my_page = $default->getPageel();
        if (!$my_page) {
            if (isset($this->settings['pagebrowser']['itemsPerPage'])) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'];
            }
            if (!$my_page) {
                $my_page = $this->settings['pagebrowser']['itemsPerPage'] = 25;
            }
        } else {
            $this->settings['pagebrowser']['itemsPerPage'] = $my_page;
        }
        $limit_from = ($currentPage-1) * $my_page;
        $limit_to = $currentPage * $my_page;

        if ($new) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $backendUserRepository = $objectManager->get(BackendUserRepository::class);
            /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $user */
            $user = $backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if ($this->request->hasArgument('delete')) {
            $deleteUids = $this->request->getArgument('delete');
            if ($deleteUids) {
                $i=0;
                foreach ($deleteUids as $uid) {
                    $this->sessionRepository->deleteRedirect($uid);
                    $i++;
                }
                $content = $i . ' entry/ies deleted.';
            }
        }

        $i = 0;
        $errorCount = 0;
        $redirectsArray = [];
        $hostsArray = [];
        $domains = $this->sessionRepository->getAllDomains();
        foreach ($domains as $domain) {
            $config = $domain->getConfiguration();
            if ($config['base']) {
                $hostsArray[] = $this->formatHost($config['base'], $my_http);
            }
        }
        $redirects = $this->sessionRepository->getRedirects();
        foreach ($redirects as $redirect) {
            $status = '?';
            $match = false;
            $uid = $redirect['uid'];
            $host = $redirect['source_host'];
            $target = $redirect['target'];
            // Wir überprüfen den Status nur für die aktuelle Seite!
            if (($i >= $limit_from) && ($i < $limit_to)) {
                if ((substr($target, 0, 1) == '/') && ($my_error != 1)) {
                    if ($host == '*') {
                        $checkHosts = $hostsArray;
                    } else {
                        $checkHosts[] = $this->formatHost($host, $my_http);
                    }
                    $errorFound = false;
                    foreach ($checkHosts as $checkHost) {
                        $headers = @get_headers($checkHost . $target);
                        if ($headers && strpos($headers[0], '200')) {
                            $status = 'OK';
                            break;
                        } else {
                            $code = intval(substr($headers[0], 9, 3));
                            if ($code) {
                                $status = $code; //$headers[0];
                            } else {
                                $status = 'none';
                            }
                            if (($code >= $my_error) && ($code < ($my_error+100))) {
                                $errorFound = true;
                            }
                        }
                    }
                    if (($status != 'OK') && $errorFound) {
                        $errorCount++;
                        $match = true;
                    }
                } else if ((substr($target, 0, 3) == 't3:') && ($my_error < 2)) {
                    $parts = explode('=', $target);
                    [$pre, $rowid] = $parts;
                    $rowid = (int)$rowid;
                    $parts = explode('?', $pre);
                    [$pre, $after] = $parts;
                    $table = substr($pre, 5);
                    if ($rowid && (($table == 'file') || ($table == 'page'))) {
                        $tableName = ($table == 'page') ? 'pages' : 'sys_file';
                        // First check, if we find a non disabled record if the check for hidden records is enabled.
                        $row = $this->sessionRepository->getRecordRow($tableName, $rowid, 'disabled');
                        if ($row === false) {
                            $status = 'target disabled or deleted!';
                            $errorCount++;
                            $match = true;
                        } else if (is_int($row['uid'])) {
                            $status = 'OK';
                        }
                    } else {
                        $status = 'unknown table ' . $table;
                    }
                } else if ((substr($target, 0, 4) == 'http') && ($my_error != 1)) {
                    $headers = @get_headers($target);
                    if (isset($headers[0])) {
                        if ($headers && strpos($headers[0], '200')) {
                            $status = 'OK';
                        } else {
                            $code = intval(substr($headers[0], 9, 3));
                            if ($code) {
                                $status = $code; //$headers[0];
                            } else {
                                $status = 'none';
                            }
                            if (($code >= $my_error) && ($code < ($my_error+100))) {
                                $errorCount++;
                                $match = true;
                            }
                        }
                    } else {
                        $status = '?';
                    }
                }
            }
            $i++;
            $redirectsArray[$uid]['uid'] = $uid;
            $redirectsArray[$uid]['host'] = $host;
            $redirectsArray[$uid]['source'] = $redirect['source_path'];
            $redirectsArray[$uid]['target'] = $target;
            if (!$my_error || $match) {
                $redirectsArray[$uid]['status'] = $status;
            } else {
                $redirectsArray[$uid]['status'] = '-';
            }
        }

        if (!$redirectsArray) $redirectsArray = [];
        $arrayPaginator = new ArrayPaginator($redirectsArray, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->view->assign('message', $content);
        $this->view->assign('redirects', $redirectsArray);
        $this->view->assign('paginator', $arrayPaginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->view->assign('my_http', $my_http);
        $this->view->assign('my_error', $my_error);
        $this->view->assign('my_page', $my_page);
        $this->view->assign('page', $currentPage);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('action', 'redirectscheck');
    }




    /**
     * Formats bytes.
     *
     * @param integer $size
     * @param integer $precision
     * @return string
     */
    function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)] .'B';
    }

    /**
     * Formats a host
     *
     * @param string $host
     * @param int $http
     * @return string
     */
    function formatHost($host, $http)
    {
        if ((strlen($host) > 2) && (substr($host,0, 4) != 'http')) {
            $pre = ($http) ? 'http://' : 'https://';
            $host = $pre . $host;
        }
        if (substr($host, -1) == '/') {
            $host = substr($host,0,-1);
        }
        return $host;
    }
}