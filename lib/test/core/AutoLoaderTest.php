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

use Vcms\FileUtils;
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
		// Just so that we can test loading directories
	}
	
	public static function clearInstance()
	{
		// Just so that we can remove the instance between tests.
		static::$instance = null;
		static::$directoryFiles = null;
		if (Registry::isKey(RegistryKeys::autoload)) {
			Registry::clear(RegistryKeys::autoload);
		}
	}
}

class AutoLoaderTest extends UnitTestCase
{
	protected $tempDir;
	
	public function __construct()
	{
		parent::__construct('AutoLoader Test');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SimpleTestCase::setup()
	 */
	public function setup()
	{
		AutoLoaderMock::getInstance();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SimpleTestCase::tearDown()
	 */
	public function tearDown()
	{
		AutoLoaderMock::clearInstance();
	}
	
	public function setupTempDir()
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
	
	public function removeTempDir()
	{
		$result = rmdir($this->tempDir);
		if ($result === false) {
			exit("Could not delete the temporary directory for testing.\n");
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
		$this->setupTempDir();
		
		// AutoLoaderMock will check the paths here so we
		// have to create valid directories
		$top = $this->tempDir;
		$path1 = AutoLoader::truepath($top.'/a');
		$path2 = AutoLoader::truepath($top.'/b');
		
		$this->assertTrue(mkdir($path1, 0777, true));
		$this->assertTrue(mkdir($path2, 0777, true));
		
		// test after instantiation
		$this->assertIdentical(array(), AutoLoader::listDirs());
		
		// Regular directory adding
		AutoLoaderMock::addDir($path1);
		$this->assertIdentical(array($path1), AutoLoader::listDirs());
		$this->assertIdentical(array($path1), Registry::get(RegistryKeys::autoload));
		
		AutoLoaderMock::addDir($path2);
		$this->assertIdentical(array($path1, $path2), AutoLoader::listDirs());
		$this->assertIdentical(
			array($path1, $path2),
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
		
		$this->assertTrue(rmdir($path1));
		$this->assertTrue(rmdir($path2));
		$this->removeTempDir();
	}
	
	/**
	 * Test single namespace only pattern.
	 */
	public function testSinglePattern()
	{
		$patterns = array(
			AutoLoaderMock::returnPattern('Vcms\FileUtils'),
			AutoLoaderMock::returnPattern('\Vcms\FileUtils')
		);
		
		// Test valid matches
		$fileNames = array(
			'vcms.fileutils', // all lower-case
			'vcms-fileutils',
			'vcms...fileutils',
			'vcms--fileutils',
			'Vcms.FileUtils', // Matching case
			'Vcms-FileUtils',
			'Vcms...FileUtils',
			'Vcms--FileUtils',
			'vCms.fileuTiLs', // improper case
			'vCms-fileuTiLs',
			'vCms...fileuTiLs',
			'vCms--fileuTiLs',
		);
		
		$size = count($fileNames);
		for ($i = 0; $i < $size; $i++) {
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
			'vcmsfileutils',
			'vcms\fileutils',
			'vcms::fileutils',
			'vcms_fileutils',
			'vcms fileutils',
			'vcms 	 fileutils',
			'.Vcms.FileUtils', // Matching case
			'.Vcms-FileUtils',
			'VcmsFileUtils',
			'Vcms\FileUtils',
			'Vcms::FileUtils',
			'Vcms_FileUtils',
			'Vcms FileUtils',
			'Vcms 	 FileUtils'
		);
		
	foreach ($badNames as $badName) {
			foreach($patterns as $pattern) {
				$num = preg_match($pattern, $badName);
				$this->assertIdentical(0, $num, "Should not match $badName");
			}
		}
	}
	
	/*
	 * Test multiple sub-namespace patterns.
	 */
	public function testMultiPattern()
	{
		$patterns = array(
			AutoLoaderMock::returnPattern('Vcms\SubOne\SubTwo\FileUtils'),
			AutoLoaderMock::returnPattern('\Vcms\SubOne\SubTwo\FileUtils')
		);
			
		// Test valid matches
		$fileNames = array(
			'vcms.subone.subtwo.fileutils', // all lower-case
			'vcms-subone-subtwo-fileutils',
			'vcms...subone...subtwo...fileutils',
			'vcms--subone--subtwo--fileutils',
			'Vcms.SubOne.SubTwo.FileUtils', // Matching case
			'Vcms-SubOne-SubTwo-FileUtils',
			'Vcms...SubOne...SubTwo...FileUtils',
			'Vcms--SubOne--SubTwo--FileUtils',
			'vCms.sUboNe.sUbtWo.fileuTiLs', // improper case
			'vCms-sUboNe-sUbtWo-fileuTiLs',
			'vCms...sUboNe...sUbtWo...fileuTiLs',
			'vCms--sUboNe--sUbtWo--fileuTiLs',
		);
		
		$size = count($fileNames);
		for ($i = 0; $i < $size; $i++) {
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
			'.Vcms.subone.subtwo.fileutils', // all lower-case
			'.Vcms-subone-subtwo-fileutils',
			'vcmssubonesubtwofileutils',
			'vcms\subone\subtwo\fileutils',
			'vcms::subone::subtwo::fileutils',
			'vcms_subone_subtwo_fileutils',
			'vcms subone subtwo fileutils',
			'vcms   subone   subtwo	 fileutils',
			'.Vcms.SubOne.SubTwo.FileUtils', // Matching case
			'.Vcms-SubOne-SubTwo-FileUtils',
			'VcmsSubOneSubTwoFileUtils',
			'Vcms\SubOne\SubTwo\FileUtils',
			'Vcms::SubOne::SubTwo::FileUtils',
			'Vcms_SubOne_SubTwo_FileUtils',
			'Vcms SubOne SubTwo FileUtils',
			'Vcms 	SubOne 	SubTwo	 FileUtils'
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
		$d = DIRECTORY_SEPARATOR;
		
		// test current working directory
		$this->assertIdentical(getcwd(), AutoLoader::truepath(''));
		
		// test . correction
		$this->assertIdentical(getcwd().$d.'a', AutoLoader::truepath('./a'));
		$this->assertIdentical(getcwd(), AutoLoader::truepath('.'));
		$this->assertIdentical(
			$this->tempDir,
			AutoLoader::truepath($this->tempDir.'/.')
		);
		$this->assertIdentical(
			$d.'a'.$d.'b'.$d.'c.d',
			AutoLoader::truepath('/a/b/c.d')
		);
		
		// test .. correction
		$this->assertIdentical(dirname(getcwd()), AutoLoader::truepath('..'));
		$this->assertIdentical(
			$this->tempDir,
			AutoLoader::truepath($this->tempDir.'/a/..')
		);
		$this->assertIdentical(
			$this->tempDir.$d.'b'.$d.'c',
			AutoLoader::truepath($this->tempDir.'/a/../b/c/d/..')
		);
			
		// test multiple directory separators
		$this->assertIdentical(
			$d.'a'.$d.'b'.$d.'c'.$d.'d',
			AutoLoader::truepath('/a//b///c//d/')
		);
	}
	
	protected function setupAutoloader()
	{
		// Set up the autoloader
		$autoloader = spl_autoload_register('\Vcms\AutoLoader::autoload');
		if (! $autoloader) {
			$this->fail('Could not attach the autoloader!');
		}
		
		// Add in the temp directory path
		AutoLoader::addDir($this->tempDir);
	}
	
	/*
	 * Writes out a simple PHP class to disk with the give class name and namespace
	 * in the specified file path.
	 */
	protected function writePHPFile($namespace, $class, $filePath)
	{
		$phpClass = "<?php\n namespace {$namespace};\n class {$class} {}\n ?>";
		if (file_put_contents($filePath, $phpClass, LOCK_EX) == false) {
			$this->fail('Could not write test PHP file.');
		}
	}
	
	public function testFlatDirLoad()
	{
		$this->setupTempDir();
		$this->setupAutoloader();
		
		// Create the class and test autoloading, remove the file once we are finished
		$path = FileUtils::truepath($this->tempDir.'/vcms.testing.atest.php');
		$this->writePHPFile('Vcms\Testing', 'ATest', $path);
		$var = new \Vcms\Testing\ATest();
		$this->assertTrue(unlink($path));
		$this->removeTempDir();
	}
	
	public function testMultiDirLoad()
	{
		$this->setupTempDir();
		$this->setupAutoloader();
		
		// Create a few sub-folders
		$dirPath = FileUtils::truepath($this->tempDir.'/a/b');
		$this->assertTrue( mkdir($dirPath, 0777, true));
		
		// Create the class and test autoloading, remove the file once we are finished
		$path = AutoLoader::truepath($dirPath.'/vcms.testing.atest.php');
		$this->writePHPFile('Vcms\Testing', 'ATest', $path);
		$var = new Vcms\Testing\ATest();
		$this->assertTrue(unlink($path));
		
		$this->assertTrue(rmdir($dirPath));
		$this->assertTrue(rmdir(dirname($dirPath)));
		$this->removeTempDir();
	}
}