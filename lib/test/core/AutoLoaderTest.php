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

use Vcms\RegistryKeys;
use Vcms\Registry;
use Vcms\AutoLoader;

class AutoLoaderMock extends AutoLoader
{
	public static function returnPattern($class)
	{
		return static::getPattern($class);
	}
	
	protected static function loadDir($directory)
	{
		// Just so that we can test fake directories
	}
}

class AutoLoaderTest extends UnitTestCase
{
	protected $tempDir;
	
	public function __construct()
	{
		parent::__construct('AutoLoader Test');
	}
	
	public function setup()
	{
		AutoLoaderMock::getInstance();
		
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
		$this->assertIsA($autoloader, 'Vcms\AutoLoader');
		$autoloader2 = $autoloader;
		$this->assertReference($autoloader, $autoloader2, 'Copy refrences are different');
	}
	
	public function testClone()
	{
		$autoloader = AutoLoader::getInstance();
		try {
			$autoloader2 = clone $autoloader;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopyException $e) {}
	}
	
	public function testAddListDirs()
	{
		// AutoLoaderMock will not actually check the paths here so we
		// can add in paths that are not valid for testing
		
		// test after instantiation
		$this->assertIdentical(array(), AutoLoader::listDirs());
		
		// Regular directory adding
		AutoLoaderMock::addDir('/1/2/3');
		$this->assertIdentical(array('/1/2/3'), AutoLoader::listDirs());
		$this->assertIdentical(array('/1/2/3'), Registry::get(RegistryKeys::autoload));
		
		AutoLoaderMock::addDir('/4/5/6');
		$this->assertIdentical(array('/1/2/3', '/4/5/6'), AutoLoader::listDirs());
		$this->assertIdentical(array('/1/2/3', '/4/5/6'),
			Registry::get(RegistryKeys::autoload)
		);
		
		AutoLoaderMock::addDir('/a/b/c');
		AutoLoaderMock::addDir('/d/e/f');
		AutoLoaderMock::addDir('/h/i/j');
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
			AutoLoaderMock::addDir(array('bad!'));
			$this->fail("Should not be able to add an array.");
		} catch (\Vcms\Exception\DataTypeException $e) {}
		
		// test empty directory
		try {
			AutoLoaderMock::addDir('');
			$this->fail("Should not be able to add an empty string.");
		} catch (\Vcms\Exception\DataTypeException $e) {}
	}
	
	public function testPattern()
	{
		$patterns = array(
			AutoLoaderMock::returnPattern('Vcms\FileUtils'),
			AutoLoaderMock::returnPattern('\Vcms\FileUtils')
		);
		
		// Test valid matches
		$fileNames = array(
			'Vcms.fileutils', // all lower-case
			'Vcms-fileutils',
			'Vcms fileutils',
			'Vcms...fileutils',
			'Vcms--fileutils',
			'Vcms 	 fileutils',
			'Vcms.FileUtils', // Matching case
			'Vcms-FileUtils',
			'Vcms FileUtils',
			'Vcms...FileUtils',
			'Vcms--FileUtils',
			'Vcms 	 FileUtils',
			'Vcms.fileuTiLs', // improper case
			'Vcms-fileuTiLs',
			'Vcms fileuTiLs',
			'Vcms...fileuTiLs',
			'Vcms--fileuTiLs',
			'Vcms 	 fileuTiLs'
		);
		
		$upTo = count($fileNames);
		for ($i = 0; $i < $upTo; $i++) {
			// make even more valid combinations
			array_push($fileNames, 'class.'.$fileNames[$i]);
			array_push($fileNames, $fileNames[$i].'.inc');
			array_push($fileNames, 'class.'.$fileNames[$i].'.inc');
			array_push($fileNames, $fileNames[$i].'.class');
			array_push($fileNames, 'class.'.$fileNames[$i].'.class');
			array_push($fileNames, $fileNames[$i].'.inc.class');
			array_push($fileNames, 'class.'.$fileNames[$i].'.inc.class');
			array_push($fileNames, $fileNames[$i].'.class.inc');
			array_push($fileNames, 'class.'.$fileNames[$i].'.class.inc');
		}
		
		foreach ($fileNames as $fileName) {
			//echo "Matching: $fileName\n";
			foreach($patterns as $pattern) {
				$num = preg_match($pattern, $fileName);
				$this->assertIdentical(1, $num, "Should match $fileName");
			}
		}
		
		// Test non-matching
		$badNames = array(
			'.Vcms.fileutils', // all lower-case
			'.Vcms-fileutils',
			'.Vcms fileutils',
			'Vcmsfileutils',
			'Vcms\fileutils',
			'Vcms::fileutils',
			'.Vcms.FileUtils', // Matching case
			'.Vcms-FileUtils',
			'.Vcms FileUtils',
			'VcmsFileUtils',
			'Vcms\FileUtils',
			'Vcms::FileUtils'
		);
		
	foreach ($badNames as $badName) {
			foreach($patterns as $pattern) {
				$num = preg_match($pattern, $badName);
				$this->assertIdentical(0, $num, "Should not match $badName");
			}
		}
	}
	
	public function testTruePath()
	{
		//TODO: implement me.
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