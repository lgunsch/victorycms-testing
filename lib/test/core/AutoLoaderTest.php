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

use VictoryCMS\AutoLoader;

class AutoLoaderTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('AutoLoader Test');
	}
	
	public function testInstance()
	{
		$autoloader = AutoLoader::getInstance();
		$this->assertIsA($autoloader, 'VictoryCMS\AutoLoader');
		$autoloader2 = $autoloader;
		$this->assertReference($autoloader, $autoloader2, 'Copy refrences are different');
	}
	
	public function testClone()
	{
		$autoloader = AutoLoader::getInstance();
		try {
			$autoloader2 = clone $autoloader;
			$this->fail('Did not throw an exception when cloning');
		} catch (VictoryCMS\Exception\SingletonCopyException $e) {}
	}
}