<?php
namespace Fixpunkt\Backendtools\Domain\Model;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Kurt Gusbeth <k.gusbeth@fixpunkt.com>, fixpunkt werbeagentur gmbh
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
 * Session
 */
class Session extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Key (action)
     *
     * @var string
     * @validate NotEmpty
     */
    protected $action = '';
    
    /**
     * Value 1
     *
     * @var int
     */
    protected $value1 = 0;
    
    /**
     * Value 2
     *
     * @var int
     */
    protected $value2 = 0;
    
    /**
     * Value 3
     *
     * @var int
     */
    protected $value3 = 0;
    
    /**
     * Value 4
     *
     * @var string
     */
    protected $value4 = '';
    
    /**
     * Value 5
     *
     * @var string
     */
    protected $value5 = '';
    
    /**
     * Value 6
     *
     * @var string
     */
    protected $value6 = '';

    /**
     * Elements per page
     *
     * @var int
     */
    protected $pageel = 0;
    
    /**
     * beuser
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser
     */
    protected $beuser = null;
    
    
    /**
     * Returns the action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Sets the action
     *
     * @param string $action
     * @return void
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    /**
     * Returns the value1
     *
     * @return int $value1
     */
    public function getValue1()
    {
        return $this->value1;
    }
    
    /**
     * Sets the value1
     *
     * @param int $value1
     * @return void
     */
    public function setValue1($value1)
    {
        $this->value1 = $value1;
    }
    
    /**
     * Returns the value2
     *
     * @return int $value2
     */
    public function getValue2()
    {
        return $this->value2;
    }
    
    /**
     * Sets the value2
     *
     * @param int $value2
     * @return void
     */
    public function setValue2($value2)
    {
        $this->value2 = $value2;
    }
    
    /**
     * Returns the value3
     *
     * @return int $value3
     */
    public function getValue3()
    {
        return $this->value3;
    }
    
    /**
     * Sets the value3
     *
     * @param int $value3
     * @return void
     */
    public function setValue3($value3)
    {
        $this->value3 = $value3;
    }
    
    /**
     * Returns the value4
     *
     * @return string $value4
     */
    public function getValue4()
    {
        return $this->value4;
    }
    
    /**
     * Sets the value4
     *
     * @param string $value4
     * @return void
     */
    public function setValue4($value4)
    {
        $this->value4 = $value4;
    }
    
    /**
     * Returns the value5
     *
     * @return string $value5
     */
    public function getValue5()
    {
        return $this->value5;
    }
    
    /**
     * Sets the value5
     *
     * @param string $value5
     * @return void
     */
    public function setValue5($value5)
    {
        $this->value5 = $value5;
    }
    
    /**
     * Returns the value6
     *
     * @return string $value6
     */
    public function getValue6()
    {
        return $this->value6;
    }
    
    /**
     * Sets the value6
     *
     * @param string $value6
     * @return void
     */
    public function setValue6($value6)
    {
        $this->value6 = $value6;
    }

    /**
     * Returns the pageel
     *
     * @return int $pageel
     */
    public function getPageel()
    {
    	return $this->pageel;
    }
    
    /**
     * Sets the pageel
     *
     * @param int $pageel
     * @return void
     */
    public function setPageel($pageel)
    {
    	$this->pageel = $pageel;
    }
    
    /**
     * Returns the beuser
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\BackendUser $beuser
     */
    public function getBeuser()
    {
        return $this->beuser;
    }
    
    /**
     * Sets the beuser
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\BackendUser $beuser
     * @return void
     */
    public function setBeuser(\TYPO3\CMS\Extbase\Domain\Model\BackendUser $beuser)
    {
        $this->beuser = $beuser;
    }

}