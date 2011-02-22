<?php
//
//  VictoryCMS - Content managment system and framework.
//
//  Copyright (C) 2010,2011	Lewis Gunsch <lgunsch@victorycms.org>
//  Copyright (C) 2010,2011	Mitchell Bosecke <mitchellbosecke@gmail.com>
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

use Vcms\ViewForge;

class ViewForgeTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('ViewForge Test');
	}
	
	public function testInstance()
	{
		$forge = ViewForge::getInstance();
		$this->assertIsA($forge, 'Vcms\ViewForge');
		$forge2 = $forge;
		$this->assertReference(
			$forge,
			$forge2,
			'Copy references are different'
		);
	}
	
	public function testClone()
	{
		$forge = ViewForge::getInstance();
		try {
			$forge2 = clone $forge;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopyException $e) {}
	}
	

}

