<?php
//
//  VictoryCMS - Content managment system and framework.
//
//  Copyright (C) 2009, 2010  Lewis Gunsch <lgunsch@victorycms.org>
//  Copyright (C) 2009 Andrew Crouse <acrouse@victorycms.org>
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

namespace VcmsTesting;

require_once 'ClassFileMapFactory.php';
require_once 'ClassFileMap.php';
require_once 'ClassFileMapAutoloader.php';

/**
 * VictoryCMS testing environment main bootstrapping class; This is the entry point
 * to the VictoryCMS testing system. It initializes a test class autoloader and runs
 * all the tests located in the lib/test directory and the app/test directory. It
 * will load all the tests, execute them, and then report on the results.
 * 
 * Normally a test folder is located in the /path/to/web/root/www/test/ directory
 * with a index.php so that it is web-reachable where test results can be seen on a
 * testing server.
 * 
 * Example: http://www.example.com/test/index.php
 * 
 * *Note*: This depends on *ClassFileMapAutoloader.php*, *ClassFileMap.php*, and
 * *ClassFileMapFactory.php* for dynamically building all the required object
 * during testing and interacting with VictoryCMS. This files should be located in
 * the same directory as VictoryCMSTestRunner.php.
 *
 * @author Lewis Gunsch
 * @author Andrew Crouse
 * @filesource
 * @package testing
 * @license http://www.gnu.org/licenses/gpl.html
 * 
 * @see http://ajbrown.org/blog/2008/12/02/an-auto-loader-using-php-tokenizer.html
 */
class VictoryCMSTestRunner
{
	/** A.J. Brown's dynamic tokenizing autoloader */
	protected $autoLoader;
	
	/** Library testing path */
	protected $libTestPath;
	
	/** application testing path */
	protected $appTestPath;
	
	/**
	 * Create a new VictoryCMSTestRunner for running all tests located in the
	 * lib/test directory and the app/test directory. All classes under the lib
	 * path and the app path will be able to be autoloaded by the recursive
	 * autoloader.
	 * 
	 * @param string $libPath should be a valid path to the VictoryCMS lib folder
	 * @param string $appPath should be a valid path to the VictoryCMS app folder
	 */
	public function __construct($libPath, $appPath)
	{
		$this->libTestPath = $libPath.DIRECTORY_SEPARATOR.'test';
		$this->appTestPath = $appPath.DIRECTORY_SEPARATOR.'test';
		
		// Ensure sanity of the lib testing path
		if (! is_dir($this->libTestPath)) {
			throw new \Exception('The lib path '.$this->libTestPath.' path is not a directory!');
		}
		if (! is_readable($this->libTestPath)) {
			throw new \Exception('The lib path '.$this->libTestPath.' is not readable');
		}
		
		// Ensure sanity of the app testing path
		if (! is_dir($this->appTestPath)) {
			throw new \Exception('The app path '.$this->appTestPath.' path is not a directory!');
		}
		if (! is_readable($this->appTestPath)) {
			throw new \Exception('The app path '.$this->appTestPath.' is not readable');
		}
		
		// build a class file map for VictoryCMS classes and testing classes
		$libPathMap = ClassFileMapFactory::generate($libPath, "lib-map");
		$appPathMap = ClassFileMapFactory::generate($appPath, "app-map");
 		
		// instanciate a new auto loader
		$this->autoLoader = new ClassFileMapAutoloader();
 
		// add the class file maps to the autoloader
		$this->autoLoader->addClassFileMap($libPathMap);
		$this->autoLoader->addClassFileMap($appPathMap);
 
		// register the autoloader
		$registered = $this->autoLoader->registerAutoload();
		if (! $registered) {
			exit('VictoryCMS could not attach the required testing autoloader!');
		}
	}
	
	/**
	 * Run the tests and report on the results.
	 */
	public function run()
	{
		$this->runTestGroupsByPath($this->libTestPath, 'lib');
		$this->runTestGroupsByPath($this->appTestPath, 'app');
	}
	
	/**
	 * Run test suites grouped by the directories and sub-directories.
	 * 
	 * @param string $testPath Path containing optional sub-directories and test cases.
	 * @param string $baseName Base-name to prepend to test suite names.
	 */
	protected function runTestGroupsByPath($testPath, $baseName)
	{
		$files = \Vcms\FileUtils::findPHPFiles($testPath);
		$testCases = $this->buildTestCaseArray($testPath, $baseName, $files);
		
		/* Run each set of test cases - each test suite is 1 directory */
		foreach ($testCases as $testName => $pathArray) {
			
			$test = $this->buildTestSuite($testName, $pathArray);
			
			/* run this test suite */
			if (\TextReporter::inCli()) {
				$test->run(new \TextReporter());
			} else {
				$test->run(new \HtmlReporter());
			}
		}
	}
	
	/**
	 * This collects the test case paths under each directory, replaces the common
	 * part of the path with the $baseName, and replace the directory separators
	 * with -.
	 * 
	 * @param string $testPath full test path to match against the files.
	 * @param string $baseName Base-name to prepend to test suite names.
	 * @param array $files of full PHP test case paths.
	 * 
	 * @return array of arrays with the key being a test case name and the value
	 * being an array of paths to be loaded for the test case.
	 */
	private function buildTestCaseArray($testPath, $baseName, $files)
	{
		$testCases = array();
		foreach ($files as $filePath) {
			if(is_file($filePath) && is_readable($filePath)) {
				$dirName = dirname($filePath);
				$dirName = str_replace($testPath, $baseName, $dirName);
				$dirName = str_replace(''.DIRECTORY_SEPARATOR, '-', $dirName);
				if (array_key_exists($dirName, $testCases)) {
					array_push($testCases[$dirName], $filePath);
				} else {
					$testCases[$dirName] = array(0 => $filePath);
				}
			}
		}
		
		return $testCases;
	}
	
	/**
	 * This creates a new TestSuite with the given name and loads it with the test
	 * any UnitTestCase classes located in the PHP files listed in the array
	 * $pathArray.
	 * 
	 * @param string $testName TestSuite name.
	 * @param array $pathArray PHP file paths to look for UnitTestSuite classes.
	 * 
	 * @return \TestSuite of UnitTestCase's.
	 */
	private function buildTestSuite($testName, $pathArray)
	{
		$test = new \TestSuite('Test Suite: '.$testName);
		foreach ($pathArray as $i => $path) {
			/* reverse-lookup the classes from each path. If the class
			 * is a UnitTestCase then add it into the test suite
			 */
			$classes = $this->autoLoader->reverseLookup($path);
			foreach ($classes as $index => $class) {
				/* If class is a singleton then it will have a private 
				 * constructor. We can determine this using a reflection class.
				 */
				$rfClass = new \ReflectionClass($class);
				$constructor = $rfClass->getConstructor();
				if (! ($constructor->isPrivate() || $constructor->isProtected())) {
					$instance = new $class;
					if ($instance instanceof \UnitTestCase) {
						$test->addFile($path);
					}	
				}
			}
		}
		return $test;
	}
}