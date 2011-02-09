<?php
//
//  VictoryCMS - Content managment system and framework.
//
//  Copyright (C) 2010  Lewis Gunsch <lgunsch@victorycms.org>
//
//  This file is part of VictoryCMS.
//
//  VictoryCMS is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 2 of the License, or
//  (at your option) any later version.
//
//  VictoryCMS is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with VictoryCMS.  If not, see <http://www.gnu.org/licenses/>.

use Vcms\LoadManager;
use Vcms\FileUtils;

/*
 * Unit test for fully testing the LoadManager.
 */
class LoadManagerTest extends UnitTestCase
{
	
	public function __construct()
	{
		parent::__construct("LoadManager Test");
	}
	
	/*
	 * Test the creation of Autoloader instances.
	 */
	public function testInstance()
	{
		$loadManager = LoadManager::getInstance();
		$this->assertIsA($loadManager, 'Vcms\LoadManager');
		$loadManager2 = $loadManager;
		$this->assertReference($loadManager, $loadManager2, 'Copy refrences are different');
	}
}
