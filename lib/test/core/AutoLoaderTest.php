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

use VictoryCMS\RegistryKeys;
use VictoryCMS\Registry;
use VictoryCMS\AutoLoader;

class AutoLoaderTest extends UnitTestCase
{
	protected $tempDir;
	
	public function __construct()
	{
		parent::__construct('AutoLoader Test');
	}
	
	public function setup()
	{
		// get a temporary unique name
		$tempName = tempnam(sys_get_temp_dir(), 'autoload_test');
		if ($tempName === false) {
			exit('Could not create a temporary directory for testing.');
		}
		
		// Delete the created unique file.
		$result = unlink($tempName);
		if ($result === false) {
			exit('Could not create a temporary directory for testing.');
		}
		
		// Use the unique name to create a unique directory
		$result = mkdir($tempName, 0777, true);
		if ($result === false) {
			exit('Could not create a temporary directory for testing.');
		}
		
		$this->tempDir = $tempName;
	}
	
	public function tearDown()
	{
		$result = rmdir($this->tempDir);
		if ($result === false) {
			exit('Could not delete the temporary directory for testing.');
		}
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
	
	public function testAddListDirs()
	{
		// AutoLoader will not actually check the paths until they are used so we
		// can add in paths that are not valid for testing
		
		// test after instantiation
		$this->assertIdentical(array(), AutoLoader::listDirs());
		
		// Regular directory adding
		AutoLoader::addDir('/1/2/3');
		$this->assertIdentical(array('/1/2/3'), AutoLoader::listDirs());
		$this->assertIdentical(array('/1/2/3'), Registry::get(RegistryKeys::autoload));
		
		AutoLoader::addDir('/4/5/6');
		$this->assertIdentical(array('/1/2/3', '/4/5/6'), AutoLoader::listDirs());
		$this->assertIdentical(array('/1/2/3', '/4/5/6'),
			Registry::get(RegistryKeys::autoload)
		);
		
		AutoLoader::addDir('/a/b/c');
		AutoLoader::addDir('/d/e/f');
		AutoLoader::addDir('/h/i/j');
		$this->assertIdentical(
			array('/1/2/3', '/4/5/6', '/a/b/c', '/d/e/f', '/h/i/j'),
			AutoLoader::listDirs()
		);
		$this->assertIdentical(
			array('/1/2/3', '/4/5/6', '/a/b/c', '/d/e/f', '/h/i/j'),
			Registry::get(RegistryKeys::autoload)
		);
		
		// test bad directory
		try {
			AutoLoader::addDir(array('bad!'));
			$this->fail("Should not be able to add an array.");
		} catch (\VictoryCMS\Exception\DataTypeException $e) {}
		
		// test empty directory
		try {
			AutoLoader::addDir('');
			$this->fail("Should not be able to add an empty string.");
		} catch (\VictoryCMS\Exception\DataTypeException $e) {}
	}
	
	public function testFlatDirLoad()
	{
		//TODO: implement me.
	}
	
	public function testMultiDirLoad()
	{
		//TODO: implement me.
	}
	
	public function testNoCaseSensitive()
	{
		//TODO: implement me.
	}
}