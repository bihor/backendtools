<?php

namespace Fixpunkt\Backendtools\Controller;

use Fixpunkt\Backendtools\Domain\Model\Session;
use Fixpunkt\Backendtools\Domain\Repository\BackendUserRepository;
use Fixpunkt\Backendtools\Domain\Repository\SessionRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2023 Kurt Gusbeth <k.gusbeth@fixpunkt.com>, fixpunkt werbeagentur gmbh
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
class SessionController extends ActionController
{
    protected int $id;

    protected ModuleTemplate $moduleTemplate;

    /**
     * sessionRepository
     *
     * @var SessionRepository
     */
    protected $sessionRepository;

    /**
     * backendUserRepository
     *
     * @var BackendUserRepository
     */
    protected $backendUserRepository;

    public function __construct(protected readonly ModuleTemplateFactory $moduleTemplateFactory, SessionRepository $sessionRepository, BackendUserRepository $backendUserRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->backendUserRepository = $backendUserRepository;
    }

    public function initializeAction(): void
    {
        $this->id = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    /**
     * action list
     *
     * @return ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('list', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('list');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue3(0);
            $default->setValue4('');
            $default->setValue5('');
            $default->setValue6('');
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('my_c')) {
            $my_c = (int)($this->request->getArgument('my_c'));		// content visibility
            $default->setValue1($my_c);
        } else {
            $my_c = $default->getValue1();
        }
        if ($this->request->hasArgument('my_p')) {
            $my_p = (int)($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else {
            $my_p = $default->getValue2();
        }
        if ($this->request->hasArgument('my_type')) {
            $my_type = (int)($this->request->getArgument('my_type'));	// type
            $default->setValue3($my_type);
        } else {
            $my_type = $default->getValue3();
        }
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// type value
            $default->setValue4($my_value);
        } else {
            $my_value = $default->getValue4();
        }
        if ($this->request->hasArgument('my_flexform')) {
            $my_flexform = $this->request->getArgument('my_flexform');	// flexform value
            $default->setValue5($my_flexform);
        } else {
            $my_flexform = $default->getValue5();
        }
        if ($this->request->hasArgument('my_exclude')) {
            $my_exclude = $this->request->getArgument('my_exclude');	// exclude type
            $default->setValue6($my_exclude);
        } else {
            $my_exclude = $default->getValue6();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_outp = (int)($this->request->getArgument('my_outp'));		// output
        } else {
            $my_outp = 0;
        }
        if ($this->request->hasArgument('my_orderby')) {
            $my_orderby = (int)($this->request->getArgument('my_orderby'));		// order by
        } else {
            $my_orderby = 0;
        }
        if ($this->request->hasArgument('my_direction')) {
            $my_direction = (int)($this->request->getArgument('my_direction'));		// order direction
        } else {
            $my_direction = 0;
        }
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $pages = $this->sessionRepository->getPagesWithExtensions(
            $my_c,
            $my_p,
            $my_type,
            $my_value,
            $my_flexform,
            $my_exclude,
            $my_orderby,
            $my_direction
        );
        $types = $this->sessionRepository->getAllTypes();
        if ($my_recursive > 0) {
            $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
        }

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->moduleTemplate->assign('my_p', $my_p);
        $this->moduleTemplate->assign('my_c', $my_c);
        $this->moduleTemplate->assign('my_type', $my_type);
        $this->moduleTemplate->assign('my_value', $my_value);
        $this->moduleTemplate->assign('my_exclude', $my_exclude);
        $this->moduleTemplate->assign('my_flexform', $my_flexform);
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_outp', $my_outp);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('my_orderby', $my_orderby);
        $this->moduleTemplate->assign('my_direction', $my_direction);
        $this->moduleTemplate->assign('rows', count($pages));
        $this->moduleTemplate->assign('pages', $pages);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('types', $types);
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'list');
        $this->addDocHeaderDropDown('list');
        return $this->moduleTemplate->renderResponse('Session/List');
    }

    /**
     * action latest
     *
     * @return ResponseInterface
     */
    public function latestAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('latest', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('latest');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue4('01.01.' . date('Y') . ' 00:00:00');
            $default->setValue5('CET');
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('my_c')) {
            $my_c = (int)($this->request->getArgument('my_c'));		// content visibility
            $default->setValue1($my_c);
        } else {
            $my_c = $default->getValue1();
        }
        if ($this->request->hasArgument('my_p')) {
            $my_p = (int)($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else {
            $my_p = $default->getValue2();
        }
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// date and time
            $default->setValue4($my_value);
        } else {
            $my_value = $default->getValue4();
        }
        if ($this->request->hasArgument('my_zone')) {
            $my_zone = $this->request->getArgument('my_zone');		    // date zone
            $default->setValue5($my_zone);
        } else {
            $my_zone = $default->getValue5();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_outp = (int)($this->request->getArgument('my_outp'));		// output
        } else {
            $my_outp = 0;
        }
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
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
            new \DateTimeZone($my_zone),
        );
        if ($d === false) {
            $tstamp = time();
        } else {
            $tstamp = $d->getTimestamp();
        }

        $pages = $this->sessionRepository->getLatestContentElements(
            $my_c,
            $my_p,
            $tstamp,
        );
        $pages2 = $this->sessionRepository->getLatestPages(
            $my_p,
            $tstamp,
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
        usort($pages, fn($a, $b) => $b['sorting'] <=> $a['sorting']);

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->moduleTemplate->assign('my_p', $my_p);
        $this->moduleTemplate->assign('my_c', $my_c);
        $this->moduleTemplate->assign('my_value', $my_value);
        $this->moduleTemplate->assign('my_zone', $my_zone);
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_outp', $my_outp);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('rows', count($pages));
        $this->moduleTemplate->assign('pages', $pages);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'latest');
        $this->addDocHeaderDropDown('latest');
        return $this->moduleTemplate->renderResponse('Session/Latest');
    }

    /**
     * action backend layouts
     *
     * @return ResponseInterface
     */
    public function layoutsAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('layouts', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('layouts');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('my_value')) {
            $my_value = $this->request->getArgument('my_value');		// date and time
            $default->setValue1($my_value);
        } else {
            $my_value = $default->getValue1();
        }
        if ($this->request->hasArgument('my_p')) {
            $my_p = (int)($this->request->getArgument('my_p'));		// pages visibility
            $default->setValue2($my_p);
        } else {
            $my_p = $default->getValue2();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_outp = (int)($this->request->getArgument('my_outp'));		// output
        } else {
            $my_outp = 0;
        }
        if ($this->request->hasArgument('my_recursive')) {
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $pages = $this->sessionRepository->getLayouts($my_value, $my_p);
        if ($my_recursive > 0) {
            $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
        }

        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        // Assign
        $this->moduleTemplate->assign('my_p', $my_p);
        $this->moduleTemplate->assign('my_value', $my_value);
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_outp', $my_outp);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('rows', count($pages));
        $this->moduleTemplate->assign('pages', $pages);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'layouts');
        $this->addDocHeaderDropDown('layouts');
        return $this->moduleTemplate->renderResponse('Session/Layouts');
    }

    /**
     * action images: images without alt- or title-tag
     *
     * @return ResponseInterface
     */
    public function imagesAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('images', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('images');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('img_without')) {
            $img_without = (int)($this->request->getArgument('img_without'));
            $default->setValue1($img_without);
        } else {
            $img_without = $default->getValue1();
        }
        if ($this->request->hasArgument('img_other')) {
            $img_other = (int)($this->request->getArgument('img_other'));
            $default->setValue2($img_other);
        } else {
            $img_other = $default->getValue2();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($img_without) {
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        } else {
            $finalArray = [];
        }
        $replacedArray = [];

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        $count = 0;

        if (($img_without == 1) && $this->request->hasArgument('replace_empty_alt')) {
            // alt-Tags setzen. In der sys_file_reference
            foreach ($finalArray as $key => $refArray) {
                $uid = $refArray['ref_uid'];
                $imgArray = $refArray['file'];
                if ($refArray['ref_title']) {
                    $finalArray[$key]['ref_alt'] = $refArray['ref_title'];
                } elseif ($imgArray['meta_title']) {
                    $finalArray[$key]['ref_alt'] = $imgArray['meta_title'];
                } else {
                    if (strrpos((string)$imgArray['name'], '.') > 0) {
                        $finalArray[$key]['ref_alt'] = trim(str_replace('_', ' ', substr((string)$imgArray['name'], 0, strrpos((string)$imgArray['name'], '.'))));
                    } else {
                        $finalArray[$key]['ref_alt'] = trim(str_replace('_', ' ', (string)$imgArray['name']));
                    }
                }
                $success = $this->sessionRepository->setAltOrTitle($uid, $finalArray[$key]['ref_alt'], '');
                if ($success) {
                    $count++;
                    $replacedArray[] = $finalArray[$key];
                }
            }
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        } elseif (($img_without == 2) && $this->request->hasArgument('replace_empty_meta')) {
            // title-Tags setzen. In der sys_file_reference
            foreach ($finalArray as $key => $refArray) {
                $uid = $refArray['ref_uid'];
                $imgArray = $refArray['file'];
                if ($refArray['ref_alt']) {
                    $finalArray[$key]['ref_title'] = $refArray['ref_alt'];
                } elseif ($imgArray['meta_alt']) {
                    $finalArray[$key]['ref_title'] = $imgArray['meta_alt'];
                } else {
                    if (strrpos((string)$imgArray['name'], '.') > 0) {
                        $finalArray[$key]['ref_title'] = trim(str_replace('_', ' ', substr((string)$imgArray['name'], 0, strrpos((string)$imgArray['name'], '.'))));
                    } else {
                        $finalArray[$key]['ref_title'] = trim(str_replace('_', ' ', (string)$imgArray['name']));
                    }
                }
                $success = $this->sessionRepository->setAltOrTitle($uid, '', $finalArray[$key]['ref_title']);
                if ($success) {
                    $count++;
                    $replacedArray[] = $finalArray[$key];
                }
            }
            $finalArray = $this->sessionRepository->getImagesWithout($img_without, $img_other);
        }
        if (count($finalArray) > 0 && $my_recursive > 0) {
            $finalArray = $this->sessionRepository->filterPagesRecursive($finalArray, $my_recursive);
        }

        if (!$finalArray) {
            $finalArray = [];
        }
        $arrayPaginator = new ArrayPaginator($finalArray, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->moduleTemplate->assign('img_without', $img_without);
        $this->moduleTemplate->assign('img_other', $img_other);
        $this->moduleTemplate->assign('count', $count);
        $this->moduleTemplate->assign('images', $finalArray);
        $this->moduleTemplate->assign('imagesReplaced', $replacedArray);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'images');
        $this->addDocHeaderDropDown('images');
        return $this->moduleTemplate->renderResponse('Session/Images');
    }

    /**
     * action missing: missing images
     *
     * @return ResponseInterface
     */
    public function missingAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('missing', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('missing');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('img_other')) {
            $img_other = (int)($this->request->getArgument('img_other'));
            $default->setValue2($img_other);
        } else {
            $img_other = $default->getValue2();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($this->request->hasArgument('delallimages')) {
            // alle nicht benutzen Bilder-Einträge löschen
            $doubleArray = $this->sessionRepository->getMissingImages($img_other);
            $notUsedImages = [];
            foreach ($doubleArray[0] as $image) {
                if (!$image['used']) {
                    $notUsedImages[] = $image;
                }
            }
            foreach ($notUsedImages as $image) {
                $uid = (int)$image['uid'];
                if ($uid) {
                    $this->sessionRepository->delMissingImage($uid);
                }
            }
            $this->addFlashMessage('All not used (in tt_content) image-entries deleted.');
        } elseif ($this->request->hasArgument('delimg') &&
            ($this->request->hasArgument('delthatimage1') || $this->request->hasArgument('delthatimage2'))) {
            // ein Bild-Eintrag löschen
            $uid = (int)$this->request->getArgument('delimg');
            if ($uid) {
                $this->sessionRepository->delMissingImage($uid);
                $this->addFlashMessage('Image-entries with uid "' . $uid . '" deleted.');
            }
        }

        $doubleArray = $this->sessionRepository->getMissingImages($img_other);
        $finalArray = $doubleArray[1];
        $notUsedImages = [];
        foreach ($doubleArray[0] as $image) {
            if (!$image['used']) {
                $notUsedImages[] = $image;
            }
        }
        $count = count($notUsedImages);

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if (count($finalArray) > 0 && $my_recursive > 0) {
            $finalArray = $this->sessionRepository->filterPagesRecursive($finalArray, $my_recursive);
        }

        if (!$finalArray) {
            $finalArray = [];
        }
        $arrayPaginator = new ArrayPaginator($finalArray, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->moduleTemplate->assign('count', $count);
        $this->moduleTemplate->assign('img_other', $img_other);
        $this->moduleTemplate->assign('images', $finalArray);
        $this->moduleTemplate->assign('fileArray', $notUsedImages);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'missing');
        $this->addDocHeaderDropDown('missing');
        return $this->moduleTemplate->renderResponse('Session/Missing');
    }

    /**
     * action pagesearch: find pages which are linked
     *
     * @return ResponseInterface
     */
    public function pagesearchAction(): ResponseInterface
    {
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('pagesearch', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('pagesearch');
            $default->setValue1(0);
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('my_c')) {
            $my_c = (int)($this->request->getArgument('my_c'));
            $default->setValue1($my_c);
        } else {
            $my_c = $default->getValue1();
        }
        if ($this->request->hasArgument('my_p')) {
            $my_p = (int)($this->request->getArgument('my_p'));
            $default->setValue2($my_p);
        } else {
            $my_p = $default->getValue2();
        }
        if ($this->request->hasArgument('exttoo')) {
            $exttoo = (int)($this->request->getArgument('exttoo'));
            $default->setValue3($exttoo);
        } else {
            $exttoo = $default->getValue3();
        }
        if ($this->request->hasArgument('linksto')) {
            $linksto = $this->request->getArgument('linksto');
            $default->setValue4($linksto);
        } else {
            $linksto = $default->getValue4();
        }
        $linkto_uid = (int)$linksto;
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
            $my_recursive = (int)($this->request->getArgument('my_recursive'));		// recursive pid search
            $default->setPagestart($my_recursive);
        } else {
            $my_recursive = $default->getPagestart();
        }

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
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
                if (ExtensionManagementUtility::isLoaded('news')) {
                    $news = $this->sessionRepository->getNewsLinks($my_c, $my_p, $linkto_uid);
                }
                if (ExtensionManagementUtility::isLoaded('camaliga')) {
                    $camaliga = $this->sessionRepository->getCamaligaLinks($my_c, $my_p, $linkto_uid);
                }
            }
            if ($my_recursive > 0) {
                $pages = $this->sessionRepository->filterPagesRecursive($pages, $my_recursive);
            }
        }

        if (!$pages) {
            $pages = [];
        }
        $arrayPaginator = new ArrayPaginator($pages, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->moduleTemplate->assign('my_c', $my_c);
        $this->moduleTemplate->assign('my_p', $my_p);
        $this->moduleTemplate->assign('linksto', $linksto);
        $this->moduleTemplate->assign('exttoo', $exttoo);
        $this->moduleTemplate->assign('pages', $pages);
        $this->moduleTemplate->assign('news', $news);
        $this->moduleTemplate->assign('camaliga', $camaliga);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('my_recursive', $my_recursive);
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'pagesearch');
        $this->addDocHeaderDropDown('pagesearch');
        return $this->moduleTemplate->renderResponse('Session/Pagesearch');
    }

    /**
     * action redirects import
     *
     * @return ResponseInterface
     */
    public function redirectsAction(): ResponseInterface
    {
        $content = '';
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('redirects', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('redirects');
            $default->setValue1(0);
            $default->setValue2(0);
            $default->setValue3(0);
            $default->setValue4('');
            $default->setValue5('0');
            $default->setValue6('301');
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('method')) {
            $method = (int)($this->request->getArgument('method'));
            $default->setValue1($method);
        } else {
            $method = $default->getValue1();
        }
        if ($this->request->hasArgument('regex')) {
            $regex = (int)($this->request->getArgument('regex'));
            $default->setValue2($regex);
        } else {
            $regex = $default->getValue2();
        }
        if ($this->request->hasArgument('convert')) {
            $convert = $this->request->getArgument('convert');
            $default->setValue5($convert);
        } else {
            $convert = $default->getValue5();
        }
        if ($this->request->hasArgument('defaultstatuscode')) {
            $defaultstatuscode = $this->request->getArgument('defaultstatuscode');
            $default->setValue6($defaultstatuscode);
        } else {
            $defaultstatuscode = $default->getValue6();
        }
        if ($this->request->hasArgument('impfile')) {
            $impfile = $this->request->getArgument('impfile');
        } else {
            $impfile = '';
        }

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if ($impfile) {
            $total = 0;
            $success = 0;
            $regexp = ($regex) ? 1 : 0;
            $treffer = [];
            $rewrites = [];
            $filename = Environment::getPublicPath() . '/' . 'fileadmin/' . $impfile;
            if (is_file($filename) && file_exists($filename)) {
                $content .= "This is the result of the file content:<br /><table>\n";
                $filecontent = fopen($filename, 'r');
                while (!feof($filecontent)) {
                    $row = trim(fgets($filecontent));
                    if ($convert == 'iso') {
                        $row = utf8_decode($row);
                    }
                    if ($convert == 'utf8') {
                        $row = utf8_encode($row);
                    }
                    $row = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($row));	// tab und/oder mehrere Spaces zu einem Space umwandeln
                    $rewrites = explode(' ', (string)$row);
                    preg_match('/R=(\d+)/', $rewrites[3], $treffer);
                    $statuscode = $treffer[1];
                    if (!$statuscode) {
                        $statuscode = (int)$defaultstatuscode;
                    }
                    if ($rewrites[1] && (!str_starts_with($rewrites[1], '^/')) && (str_starts_with($rewrites[1], '^'))) {
                        if ($regexp) {
                            $rewrites[1] = '^/' . substr($rewrites[1], 1);    // aus ^xyz wird ^/xyz
                        } else {
                            $rewrites[1] = '/' . substr($rewrites[1], 1);    // aus ^xyz wird /xyz
                        }
                    }
                    if ($regexp) {
                        $rewrites[1] = '#' . $rewrites[1] . '#';        // TYPO3 will das so
                    }
                    if ($rewrites[1] && $rewrites[2] && (strlen($rewrites[1]) > 2)) {
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
                fclose($filecontent);
                $content .= "</table><br />$success/$total lines ";
                $content .= ($method) ? 'added.' : 'accepted.';
            } else {
                $content .= 'Note: file not found!!!';
            }
        }
        $this->moduleTemplate->assign('method', $method);
        $this->moduleTemplate->assign('regex', $regex);
        $this->moduleTemplate->assign('defaultstatuscode', $defaultstatuscode);
        $this->moduleTemplate->assign('convert', $convert);
        $this->moduleTemplate->assign('impfile', $impfile);
        $this->moduleTemplate->assign('message', $content);
        $this->moduleTemplate->assign('action', 'redirects');
        $this->addDocHeaderDropDown('redirects');
        return $this->moduleTemplate->renderResponse('Session/Redirects');
    }

    /**
     * action redirects check
     *
     * @return ResponseInterface
     */
    public function redirectscheckAction(): ResponseInterface
    {
        $content = '';
        $beuser_id = $GLOBALS['BE_USER']->user['uid'];
        $result = $this->sessionRepository->findByAction('redirectscheck', $beuser_id);
        if ($result->count() == 0) {
            $new = true;
            $default = GeneralUtility::makeInstance(Session::class);
            $default->setAction('redirectscheck');
            $default->setValue1(0);
            $default->setValue2(0);
        } else {
            $new = false;
            $default = $result[0];
        }

        if ($this->request->hasArgument('currentPage')) {
            $currentPage = (int)($this->request->getArgument('currentPage'));
        } else {
            $currentPage = 1;
        }
        if ($this->request->hasArgument('my_http')) {
            $my_http = (int)($this->request->getArgument('my_http'));
            $default->setValue1($my_http);
        } else {
            $my_http = $default->getValue1();
        }
        if ($this->request->hasArgument('my_error')) {
            $my_error = (int)($this->request->getArgument('my_error'));
            $default->setValue2($my_error);
        } else {
            $my_error = $default->getValue2();
        }
        if ($this->request->hasArgument('my_page')) {
            $my_page = (int)($this->request->getArgument('my_page'));		// elements per page
            $default->setPageel($my_page);
        } else {
            $my_page = $default->getPageel();
        }
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
        $limit_from = ($currentPage - 1) * $my_page;
        $limit_to = $currentPage * $my_page;

        if ($new) {
            $user = $this->backendUserRepository->findByUid($beuser_id);
            $default->setBeuser($user);
            $this->sessionRepository->add($default);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        } else {
            $this->sessionRepository->update($default);
        }

        if ($this->request->hasArgument('delete')) {
            $deleteUids = $this->request->getArgument('delete');
            if ($deleteUids) {
                $i = 0;
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
                if ((str_starts_with((string)$target, '/')) && ($my_error != 1)) {
                    if ($host == '*') {
                        $checkHosts = $hostsArray;
                    } else {
                        $checkHosts[] = $this->formatHost($host, $my_http);
                    }
                    $errorFound = false;
                    foreach ($checkHosts as $checkHost) {
                        $headers = @get_headers($checkHost . $target);
                        if (is_array($headers) && isset($headers[0])) {
                            if (strpos((string)$headers[0], '200')) {
                                $status = 'OK';
                                break;
                            } else {
                                $code = (int)(substr((string)$headers[0], 9, 3));
                                if ($code) {
                                    $status = $code; //$headers[0];
                                } else {
                                    $status = 'none';
                                }
                                if (($code >= $my_error) && ($code < ($my_error + 100))) {
                                    $errorFound = true;
                                }
                            }
                        }
                    }
                    if (($status != 'OK') && $errorFound) {
                        $errorCount++;
                        $match = true;
                    }
                } elseif ((str_starts_with((string)$target, 't3:')) && ($my_error < 2)) {
                    $parts = explode('=', (string)$target);
                    [$pre, $rowid] = $parts;
                    $targetHash = strpos((string)$rowid, '#');
                    if ($targetHash) {
                        $rowid = substr($rowid, 0, $targetHash);
                    }
                    $rowid = (int)$rowid;
                    $parts = explode('?', $pre);
                    [$pre, $after] = $parts;
                    $table = substr($pre, 5);
                    if ($rowid && (($table == 'file') || ($table == 'page'))) {
                        $tableName = ($table == 'page') ? 'pages' : 'sys_file';
                        // First check, if we find a not disabled record if the check for hidden records is enabled.
                        $row = $this->sessionRepository->getRecordRow($tableName, $rowid, 'disabled');
                        if ($row === false) {
                            $status = 'target disabled or deleted!';
                            $errorCount++;
                            $match = true;
                        } elseif (is_int($row['uid'])) {
                            if (isset($row['doktype']) && ($row['doktype'] == 254 || $row['doktype'] == 255 || $row['doktype'] == 198 || $row['doktype'] == 199)) {
                                $status = 'doktype=' . $row['doktype'] . ' !';
                            } elseif (($table == 'page') && ($redirect['source_path'] == $row['slug'])) {
                                $status = 'source=target!';
                            } else {
                                $status = 'OK';
                            }
                        }
                    } else {
                        $status = 'unknown table ' . $table;
                    }
                } elseif ((str_starts_with((string)$target, 'http')) && ($my_error != 1)) {
                    $headers = @get_headers($target);
                    if (is_array($headers) && isset($headers[0])) {
                        if (strpos((string)$headers[0], '200')) {
                            $status = 'OK';
                        } else {
                            $code = (int)(substr((string)$headers[0], 9, 3));
                            if ($code) {
                                $status = $code; //$headers[0];
                            } else {
                                $status = 'none';
                            }
                            if (($code >= $my_error) && ($code < ($my_error + 100))) {
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

        if (!$redirectsArray) {
            $redirectsArray = [];
        }
        $arrayPaginator = new ArrayPaginator($redirectsArray, $currentPage, $this->settings['pagebrowser']['itemsPerPage']);
        $pagination = new SimplePagination($arrayPaginator);

        $this->moduleTemplate->assign('message', $content);
        $this->moduleTemplate->assign('redirects', $redirectsArray);
        $this->moduleTemplate->assign('paginator', $arrayPaginator);
        $this->moduleTemplate->assign('pagination', $pagination);
        $this->moduleTemplate->assign('no_pages', range(1, $pagination->getLastPageNumber()));
        $this->moduleTemplate->assign('my_http', $my_http);
        $this->moduleTemplate->assign('my_error', $my_error);
        $this->moduleTemplate->assign('my_page', $my_page);
        $this->moduleTemplate->assign('page', $currentPage);
        $this->moduleTemplate->assign('settings', $this->settings);
        $this->moduleTemplate->assign('action', 'redirectscheck');
        $this->addDocHeaderDropDown('redirectscheck');
        return $this->moduleTemplate->renderResponse('Session/Redirectscheck');
    }

    /**
     * Formats bytes.
     *
     * @param int $size
     * @param int $precision
     * @return string
     */
    public function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = ['', 'k', 'M', 'G', 'T'];

        return round(1024 ** ($base - floor($base)), $precision) . ' ' . $suffixes[floor($base)] . 'B';
    }

    /**
     * Formats a host
     *
     * @param string $host
     * @param int $http
     * @return string
     */
    public function formatHost($host, $http)
    {
        if ((strlen($host) > 2) && (!str_starts_with($host, 'http'))) {
            $pre = ($http) ? 'http://' : 'https://';
            $host = $pre . $host;
        }
        if (str_ends_with($host, '/')) {
            $host = substr($host, 0, -1);
        }
        return $host;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function addDocHeaderDropDown(string $currentAction): void
    {
        $languageService = $this->getLanguageService();
        $actionMenu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $actionMenu->setIdentifier('backendtoolsSelector');
        $actions = ['list', 'latest', 'pagesearch', 'layouts', 'images', 'missing', 'redirects', 'redirectscheck'];
        foreach ($actions as $action) {
            $actionMenu->addMenuItem(
                $actionMenu->makeMenuItem()
                    ->setTitle($languageService->sL(
                        'LLL:EXT:backendtools/Resources/Private/Language/locallang_mod1.xlf:module.' . $action,
                    ))
                    ->setHref($this->getModuleUri($action))
                    ->setActive($currentAction === $action),
            );
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($actionMenu);
    }

    protected function getModuleUri(string $action = null, array $additionalPramaters = []): string
    {
        $parameters = [
            'id' => $this->id,
        ];
        if ($action !== null) {
            $parameters['action'] = $action;
        }
        return $this->uriBuilder->uriFor($action, null, 'Session', 'mod1');
    }
}
