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
use Vcms\Autoloader;

/*
 * AutoloaderMock extends the Autoloader so that protetected methods can be fully
 * tested.
 */
class AutoloaderMock extends Autoloader
{
	/*
	 * Returns the pattern produced by the protected method getPattern().
	 */
	public static function returnPattern($class)
	{
		return static::getPattern($class);
	}

	/*
	 * Necessary so that we can test loading directories.
	 */
	protected static function loadDir($directory)
	{
		// Just so that we can test loading directories
	}

	/*
	 * Removes the autoloader instance.
	 */
	public static function clearInstance()
	{
		// Just so that we can remove the instance between tests.
		static::$instance = null;
		static::$directoryFilesCache = array();
		static::$ignoreDirs = array();
		static::$searchDirs = array();
	}
}

/*
 * Unit test for fully testing the autoloader.
 */
class AutoloaderTest extends UnitTestCase
{
	protected $tempDir;

	public function __construct()
	{
		parent::__construct('Autoloader Test');
	}

	/*
	 * Setup the autoloader instance.
	 */
	public function setup()
	{
		AutoloaderMock::getInstance();
	}

	/*
	 * Remove the autoloader instance.
	 */
	public function tearDown()
	{
		AutoloaderMock::clearInstance();
	}

	/*
	 * Create a temporary directory to work out of.
	 */
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

	/*
	 * Remove the temporary directory.
	 */
	public function removeTempDir()
	{
		$result = rmdir($this->tempDir);
		if ($result === false) {
			exit("Could not delete the temporary directory for testing.\n");
		}
	}

	/*
	 * Test the creation of Autoloader instances.
	 */
	public function testInstance()
	{
		$autoloader = Autoloader::getInstance();
		$this->assertIsA($autoloader, 'Vcms\Autoloader');
		$autoloader2 = $autoloader;
		$this->assertReference($autoloader, $autoloader2, 'Copy refrences are different');
	}

	/*
	 * Test throwing exception when cloning a singleton.
	 */
	public function testClone()
	{
		$autoloader = Autoloader::getInstance();
		try {
			$autoloader2 = clone $autoloader;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopy $e) {}
	}

	/*
	 * Test adding and listing directories saved in the autoloader.
	 */
	public function testAddListDirs()
	{
		$this->setupTempDir();

		// AutoLoaderMock will check the paths here so we
		// have to create valid directories
		$top = $this->tempDir;
		$path1 = Autoloader::truepath($top.'/a');
		$path2 = Autoloader::truepath($top.'/b');

		$this->assertTrue(mkdir($path1, 0777, true));
		$this->assertTrue(mkdir($path2, 0777, true));

		// test after instantiation
		$this->assertIdentical(array(), Autoloader::listDirs());

		// Regular directory adding
		AutoloaderMock::addDir($path1);
		$this->assertIdentical(array($path1), Autoloader::listDirs());

		AutoloaderMock::addDir($path2);
		$this->assertIdentical(array($path1, $path2), Autoloader::listDirs());

		// test bad directory
		try {
			AutoloaderMock::addDir(array('bad!'));
			$this->fail("Should not be able to add an array.");
		} catch (\Vcms\Exception\InvalidType $e) {}

		// test empty directory
		try {
			AutoloaderMock::addDir('');
			$this->fail("Should not be able to add an empty string.");
		} catch (\Vcms\Exception\InvalidValue $e) {}

		$this->assertTrue(rmdir($path1));
		$this->assertTrue(rmdir($path2));
		$this->removeTempDir();
	}

	/*
	 * Test single namespace only regular expression pattern.
	 */
	public function testSinglePattern()
	{
		$patterns = array(
			AutoloaderMock::returnPattern('Vcms\FileUtils'),
			AutoloaderMock::returnPattern('\Vcms\FileUtils')
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
			foreach ($patterns as $pattern) {
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
			foreach ($patterns as $pattern) {
				$num = preg_match($pattern, $badName);
				$this->assertIdentical(0, $num, "Should not match $badName");
			}
		}
	}

	/*
	 * Test multiple sub-namespace regular expression patterns.
	 */
	public function testMultiPattern()
	{
		$patterns = array(
			AutoloaderMock::returnPattern('Vcms\SubOne\SubTwo\FileUtils'),
			AutoloaderMock::returnPattern('\Vcms\SubOne\SubTwo\FileUtils')
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
			foreach ($patterns as $pattern) {
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
			foreach ($patterns as $pattern) {
				$num = preg_match($pattern, $badName);
				$this->assertIdentical(0, $num, "Should not match $badName");
			}
		}
	}

	/*
	 * Test file system path correction.
	 */
	public function testTruePath()
	{
		$d = DIRECTORY_SEPARATOR;

		// test current working directory
		$this->assertIdentical(getcwd(), Autoloader::truepath(''));

		// test . correction
		$this->assertIdentical(getcwd().$d.'a', Autoloader::truepath('./a'));
		$this->assertIdentical(getcwd(), Autoloader::truepath('.'));
		$this->assertIdentical(
			$this->tempDir,
			Autoloader::truepath($this->tempDir.'/.')
		);
		$this->assertIdentical(
			$d.'a'.$d.'b'.$d.'c.d',
			Autoloader::truepath('/a/b/c.d')
		);

		// test .. correction
		$this->assertIdentical(dirname(getcwd()), Autoloader::truepath('..'));
		$this->assertIdentical(
			$this->tempDir,
			Autoloader::truepath($this->tempDir.'/a/..')
		);
		$this->assertIdentical(
			$this->tempDir.$d.'b'.$d.'c',
			Autoloader::truepath($this->tempDir.'/a/../b/c/d/..')
		);

		// test multiple directory separators
		$this->assertIdentical(
			$d.'a'.$d.'b'.$d.'c'.$d.'d',
			Autoloader::truepath('/a//b///c//d/')
		);
	}

	/*
	 * Creates and initializes a test autoloader with the temporary directory.
	 */
	protected function setupAutoloader()
	{
		// Set up the autoloader
		$autoloader = spl_autoload_register('\Vcms\Autoloader::autoload');
		if (! $autoloader) {
			$this->fail('Could not attach the autoloader!');
		}

		// Add in the temp directory path
		Autoloader::addDir($this->tempDir);
		Autoloader::scanDirs();
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

	/*
	 * Test autoloading a class directly in the autoloaders path.
	 */
	public function testFlatDirLoad()
	{
		$this->setupTempDir();

		// Create the class and test autoloading, remove the file once we are finished
		$path = FileUtils::truepath($this->tempDir.'/vcms.testing.atest.php');
		$this->writePHPFile('Vcms\Testing', 'ATest', $path);
		$this->setupAutoloader();
		$var = new \Vcms\Testing\ATest();

		$this->assertTrue(unlink($path));
		$this->removeTempDir();
	}

	/*
	 * Test autoloading a class in a sub-directory of a directory in the autoloaders
	 * path.
	 */
	public function testMultiDirLoad()
	{
		$this->setupTempDir();

		// Create a few sub-folders
		$dirPath = FileUtils::truepath($this->tempDir.'/a/b');
		$this->assertTrue(mkdir($dirPath, 0777, true));

		// Create the class and test autoloading, remove the file once we are finished
		$path = Autoloader::truepath($dirPath.'/vcms.testing.atest.php');
		$this->writePHPFile('Vcms\Testing', 'ATest', $path);
		$this->setupAutoloader();
		$var = new Vcms\Testing\ATest();

		$this->assertTrue(unlink($path));
		$this->assertTrue(rmdir($dirPath));
		$this->assertTrue(rmdir(dirname($dirPath)));
		$this->removeTempDir();
	}
}