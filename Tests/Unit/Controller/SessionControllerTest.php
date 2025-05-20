<?php

namespace Fixpunkt\Backendtools\Tests\Unit\Controller;

use Fixpunkt\Backendtools\Controller\SessionController;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Kurt Gusbeth <k.gusbeth@fixpunkt.com>, fixpunkt werbeagentur gmbh
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class Fixpunkt\Backendtools\Controller\SessionController.
 *
 * @author Kurt Gusbeth <k.gusbeth@fixpunkt.com>
 */
class SessionControllerTest extends UnitTestCase
{
    /**
  * @var SessionController
  */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMock(SessionController::class, ['redirect', 'forward', 'addFlashMessage'], [], '', false);
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function listActionFetchesAllSessionsFromRepositoryAndAssignsThemToView(): void
    {

        $allSessions = $this->getMock(ObjectStorage::class, [], [], '', false);

        $sessionRepository = $this->getMock('', ['findAll'], [], '', false);
        $sessionRepository->expects(self::once())->method('findAll')->willReturn($allSessions);
        $this->inject($this->subject, 'sessionRepository', $sessionRepository);

        $view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
        $view->expects(self::once())->method('assign')->with('sessions', $allSessions);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }
}
