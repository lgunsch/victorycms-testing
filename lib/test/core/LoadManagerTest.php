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

class LoadManagerTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('LoadManager Test');
	}
	
	public function testInstance()
	{
		$loader = LoadManager::getInstance();
		$this->assertIsA($loader, 'Vcms\LoadManager');
		$loader2 = $loader;
		$this->assertReference($loader, $loader2, 'Copy refrences are different');
	}
	
	public function testClone()
	{
		$loader = LoadManager::getInstance();
		try {
			$loader2 = clone $loader;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopyException $e) {}
	}
	
	public function testSingleLoad()
	{
		$loader = LoadManager::getInstance();
		$config = tmpfile(); // creates a temporary file
		fwrite($config, "{\"load\":\"./config2.json\"}");
		$config2 = tmpfile();
		fwrite($config, "{\"testing\":\"complete\"}");
		try{
			LoadManager::load("config.json");
		}
		catch(Exception $e){
			$this->fail('Threw an exception while loading a single config file.');
		}
		fclose($config); // closes and deletes temporary files
		fclose($config);
		
	}
	
	public function testMultiLoad()
	{
		//TODO: implement me.
	}
	
	public function testBadJson()
	{
		//TODO: implement me.
	}
	
	public function testBadFilePath()
	{
		//TODO: implement me.
	}
}