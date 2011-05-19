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

use Vcms\LoadManager;
use Vcms\Registry;

/*
 * Unit test for fully testing the load manager.
 */
class LoadManagerTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('LoadManager Test');
	}

	/*
	 * Test the creation of LoadManager instances.
	 */
	public function testInstance()
	{
		$loader = LoadManager::getInstance();
		$this->assertIsA($loader, 'Vcms\LoadManager');
		$loader2 = $loader;
		$this->assertReference(
			$loader,
			$loader2,
			'Copy refrences are different'
		);
	}

	/*
	 * Test throwing an exception when cloning a singleton.
	 */
	public function testClone()
	{
		$loader = LoadManager::getInstance();
		try {
			$loader2 = clone $loader;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopy $e) {}
	}

	/*
	 * Test loading a single configuration file.
	 */
	public function testSingleLoad()
	{
		$loader = LoadManager::getInstance();

		$config1 = tempnam("./", "config1"); // creates a temporary file
		$config2 = tempnam("./", "config2");
		$handle1 = fopen($config1, "w");
		$handle2 = fopen($config2, "w");
		fwrite($handle1, "{\"load\":\"$config2\"}");
		fwrite($handle2, "{
							\"setting1\":\"success\",
							\"setting2\":{
									\"value\":\"success\",
									\"readonly\":true
								}
							}");

		try {
			LoadManager::load($config1);
		} catch(Exception $e) {
			$this->fail('Threw an exception while loading a single config file.');
		}
		// Check that the correct values of config2 are actually being put in Registry
		$this->assertTrue(
			Registry::isReadOnly("setting2"),
			"Read only value not loading properly via LoadManager"
		);
		$value = Registry::get("setting2");
		$this->assertEqual(
			$value[0],
			"success", "Values are loading into Registry properly via LoadManager"
		);

		fclose($handle1);
		fclose($handle2);
		unlink($config1);
		unlink($config2);
	}

	/*
	 * Test loading a configuration file which loads multiple other configuration
	 * files.
	 */
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
		fwrite($handle2, "{\"firstfile\":\"success1\"}");
		fwrite($handle3, "{\"secondfile\":\"success2\"}");

		try {
			LoadManager::load($config1);
		} catch(Exception $e) {
			$this->fail('Threw an exception while loading multiple config files.');
		}

		$value = Registry::get("secondfile");

		$this->assertEqual(
			$value,
			"success2", "Values are loading into Registry properly via LoadManager"
		);

		fclose($handle1);
		fclose($handle2);
		fclose($handle3);
		unlink($config1);
		unlink($config2);
		unlink($config3);
	}

	/*
	 * Test throwing an exception during loading a bad configuration file.
	 */
	public function testBadJson()
	{
		$loader = LoadManager::getInstance();

		$config1 = tempnam("./", "config1");
		$handle1 = fopen($config1, "w");
		fwrite($handle1, "{\"load:\"$config1\"}"); // json missing a quotation

		try {
			LoadManager::load($config1);
			$this->fail('Did not throw an exception with a bad JSON file.');
		} catch(Exception $e) {}

		fclose($handle1);
		unlink($config1);
	}

	/*
	 * Test throwing an exception for a bad path to a configuration file.
	 */
	public function testBadFilePath()
	{
		$loader = LoadManager::getInstance();

		$config1 = tempnam("./", "config1");
		$handle1 = fopen($config1, "w");
		fwrite($handle1, "{\"load:\"".$config1."nonexisting\"}"); // non existing filepath

		try{
			LoadManager::load($config1);
			$this->fail('Did not throw an exception with a configuration path.');
		} catch(Exception $e) {}

		fclose($handle1);
		unlink($config1);
	}

	/*
	 * Test loading configuration files which reference each other recursively.
	 */
	public function testRecursiveLoad()
	{
		$loader = LoadManager::getInstance();

		$config1 = tempnam("./", "config1");
		$config2 = tempnam("./", "config2");
		$handle1 = fopen($config1, "w");
		$handle2 = fopen($config2, "w");
		fwrite($handle1, "{\"load\":[\"$config2\"], \"config1\":\"success1\"}");
		fwrite($handle2, "{\"load\":[\"$config1\"], \"config2\":\"success2\"}");

		try {
			LoadManager::load($config1);
		} catch(Exception $e) {
			$this->fail('Threw an exception while loading config files.');
		}

		$value1 = Registry::get("config1");
		$value2 = Registry::get("config2");
		// this should not be an array since there is only one value
		// recursive loading should not make it an array
		$this->assertEqual($value1, "success1");
		$this->assertEqual($value2, "success2");

		fclose($handle1);
		fclose($handle2);
		unlink($config1);
		unlink($config2);
	}
}

