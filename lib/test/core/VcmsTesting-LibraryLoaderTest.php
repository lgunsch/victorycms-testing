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

use Vcms\LibraryLoader;


/*
 * Unit test for testing the Library Loader.
 */
class LibraryLoaderTest extends UnitTestCase
{
	protected $tempDir;
	
	public function __construct()
	{
		parent::__construct('Library Loader Test');
	}
	
	/*
	 * Create a temporary directory with an external library.
	 */
	public function setupTempLibrary()
	{
		//TODO: Implement
	}
	
	/*
	 * Remove the temporary directory.
	 */
	public function removeTempDir()
	{
		//TODO: Implement
	}
	
	/*
	 * Test the creation of Autoloader instances.
	 */
	public function testInstance()
	{
		//TODO: Implement
	}
	
	/*
	 * Test throwing exception when cloning a singleton.
	 */
	public function testClone()
	{
		//TODO: Implement
	}
	
	/*
	 * Test loading an external library
	 */
	public function testLibrary()
	{
		//TODO: implement
	}
	
	/*
	 * Test loading an external library with a config file
	 */
	public function testLibraryWithConfig()
	{
		//TODO: implement
	}
	
	/*
	 * Test loading an external library that doesn't exist.
	 */
	public function testNonExistingLibrary()
	{
		//TODO: implement
	}
	
	/*
	 * Test loading an external library that is set without a
	 * class name in the main config file.
	 * An exception is expected to be thrown. 
	 */
	public function testLibraryWithoutClass()
	{
		//TODO: implement
	}
	
	/*
	 * Test loading an external file that is not extending AbstractLibraryInit. 
	 * An exception is expected to be thrown. 
	 */
	public function testLibraryNotExtendingAbstractInit()
	{
		//TODO: implement
	}

}