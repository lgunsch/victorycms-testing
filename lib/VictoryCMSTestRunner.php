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

namespace VictoryCMSTesting;

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
		
		//print_r($this->autoLoader);
	}
	
	/**
	 * Run the tests and report on the results.
	 */
	public function run()
	{
		$this->runLibTest();
		$this->runAppTest();
	}

	protected function runLibTest()
	{
		$path = realpath($this->libTestPath);
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($path),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		
		//print_r($files);
	}
	
	protected function runAppTest()
	{
		$path = realpath($this->appTestPath);
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($path),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		
		//print_r($files);
	}
}