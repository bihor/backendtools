<?php

namespace Fixpunkt\Backendtools\Tests\Unit\Domain\Model;

use TYPO3\CMS\Core\Tests\UnitTestCase;
use Fixpunkt\Backendtools\Domain\Model\Session;
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
 * Test case for class \Fixpunkt\Backendtools\Domain\Model\Session.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Kurt Gusbeth <k.gusbeth@fixpunkt.com>
 */
class SessionTest extends UnitTestCase
{
	/**
  * @var Session
  */
 protected $subject = NULL;

	public function setUp()
	{
		$this->subject = new Session();
	}

	public function tearDown()
	{
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getActionReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getAction()
		);
	}

	/**
	 * @test
	 */
	public function setActionForStringSetsAction()
	{
		$this->subject->setAction('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'action',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getValue1ReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setValue1ForIntSetsValue1()
	{	}

	/**
	 * @test
	 */
	public function getValue2ReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setValue2ForIntSetsValue2()
	{	}

	/**
	 * @test
	 */
	public function getValue3ReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setValue3ForIntSetsValue3()
	{	}

	/**
	 * @test
	 */
	public function getValue4ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getValue4()
		);
	}

	/**
	 * @test
	 */
	public function setValue4ForStringSetsValue4()
	{
		$this->subject->setValue4('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'value4',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getValue5ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getValue5()
		);
	}

	/**
	 * @test
	 */
	public function setValue5ForStringSetsValue5()
	{
		$this->subject->setValue5('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'value5',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getValue6ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getValue6()
		);
	}

	/**
	 * @test
	 */
	public function setValue6ForStringSetsValue6()
	{
		$this->subject->setValue6('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'value6',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getBeuserReturnsInitialValueForBackendUser()
	{	}

	/**
	 * @test
	 */
	public function setBeuserForBackendUserSetsBeuser()
	{	}
}
