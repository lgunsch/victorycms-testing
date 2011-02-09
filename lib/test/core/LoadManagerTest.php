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
		$config1 = tempnam("./", "config1"); // creates a temporary file
		$config2 = tempnam("./", "config2");
		$handle1 = fopen($config1, "w");
		$handle2 = fopen($config2, "w");
		fwrite($handle1, "{\"load\":\"$config2\"}");
		fwrite($handle2, "{\"firstfile\":\"success\"}");
		try{
			LoadManager::load($config1);
		}
		catch(Exception $e){
			$this->fail('Threw an exception while loading a single config file.');
		}
		fclose($handle1); 
		fclose($handle2);
		unlink($config1); 
		unlink($config2);
	}
	
	public function testMultiLoad()
	{
		$loader = LoadManager::getInstance();
		$config1 = tempnam("./", "config1"); 
		$config2 = tempnam("./", "config2");
		$config3 = tempnam("./", "config3");
		$handle1 = fopen($config1, "w");
		$handle2 = fopen($config2, "w");
		$handle3 = fopen($config3, "w");
		fwrite($handle1, "{\"load\":[\"$config2\",\"$config3\"]}");
		fwrite($handle2, "{\"firstfile\":\"success\"}");
		fwrite($handle3, "{\"secondfile\":\"success\"}");
		try{
			LoadManager::load($config1);
		}
		catch(Exception $e){
			$this->fail('Threw an exception while loading multiple config files.');
		}
		fclose($handle1); 
		fclose($handle2);
		fclose($handle3);
		unlink($config1); // delete temporary files
		unlink($config2);
		unlink($config3);
		
	}
	
	public function testBadJson()
	{
		$loader = LoadManager::getInstance();
		$config1 = tempnam("./", "config1"); // creates a temporary file
		$handle1 = fopen($config1, "w");
		fwrite($handle1, "{\"load:\"$config2\"}"); // json missing a quotation
		try{
			LoadManager::load($config1);
			$this->fail('Did not throw an exception with a bad JSON file.');
		}
		catch(Exception $e){}
		fclose($handle1); 
		unlink($config1); 
	}
	
	public function testBadFilePath()
	{
		//TODO: implement me.
	}
}