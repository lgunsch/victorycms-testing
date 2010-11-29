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
 * all the tests under the testing folder, 'test', directory heirarchy. It will load
 * all the tests, execute them, and then report on the results.
 * 
 * *Note*: $path should be a valid web root path including the directories lib,
 * app, and www and all thier sub directories. Normally the test folder is
 * under the $path/www/test directory including index.php so that it is web-reachable
 * where test results can be seen on a testing server.
 * 
 * *Note*: This depends on *ClassFileMapAutoloader.php*, *ClassFileMap.php*, and
 * *ClassFileMapFactory.php* for dynamically building all the required object
 * during testing and interacting with VictoryCMS. This files should be located in
 * the same directory as VictoryCMSTestRunner.php.
 * 
 * Example:
 * http://www.example.com/test/index.php
 * 
 * Where http://www.example.com/ is the location of the www directory the of VictoryCMS
 * system.
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
	
	/**
	 * Create a new VictoryCMSTestRunner for running all tests located under the
	 * test folder.
	 * 
	 * @param string $path should be a valid path web root path including the directories lib,
	 * app, and www and all thier sub directories.
	 */
	public function __construct($path)
	{
		// build a class file map for VictoryCMS classes and testing classes
		$appClassFileMap = ClassFileMapFactory::generate($path);
 
		//print_r($appClassFileMap);
		
		// instanciate a new auto loader
		$this->autoLoader = new ClassFileMapAutoloader();
 
		// add the class file map to the autoloader
		$this->autoLoader->addClassFileMap( $appClassFileMap );
 
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
		
	}
}