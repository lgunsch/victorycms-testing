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

use VictoryCMS\RegistryNode;
use VictoryCMS\Registry;

class RegistryTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('Registry Test');
	}
	
	public function testInstance()
	{
		$reg = Registry::getInstance();
		$this->assertIsA($reg, 'VictoryCMS\Registry');
		$reg2 = $reg;
		$this->assertReference($reg, $reg2, 'Copy refrences are different');
	}
	
	public function testClone()
	{
		$reg = Registry::getInstance();
		try {
			$reg2 = clone $reg;
			$this->fail('Did not throw an exception when cloning');
		} catch (VictoryCMS\Exception\SingletonCopyException $e) {}
	}
	
	public function testSetGet()
	{
		/* Test null binding and value exceptions */
		try {
			Registry::set(null, "value");
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		try {
			Registry::set("key", null);
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* test bad boolean */
		try {
			Registry::set("key", "string", array());
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* Test regular setting and getting */
		Registry::set("key", 5);
		$this->assertIdentical(5, Registry::get("key"));
		Registry::set("key", "string");
		$this->assertIdentical("string", Registry::get("key"));
		$val = array('me1', "me2", "me3");
		Registry::set("key", $val);
		$this->assertIdentical($val, Registry::get('key'));
		
		/* Test overwritting */
		try {
			Registry::set("test-set-get-key", 5, true);
			Registry::set("test-set-get-key", 6);
			$this->fail("Readonly key should not be overwritten!");
		} catch (\VictoryCMS\Exception\OverwriteException $e) {}
	}
	
	public function testAddGet()
	{
		/* Test null binding and value */
		try {
			Registry::add(null, "value");
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		try {
			Registry::add("key", null);
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* test bad boolean */
		try {
			Registry::add("key", "string", array());
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* Test regular add and get */
		$expected = array();
		Registry::clear("key");
		Registry::add("key", 5);
		array_push($expected, 5);
		$this->assertIdentical($expected, Registry::get("key"));
		Registry::add("key", "string");
		array_push($expected, "string");
		$this->assertIdentical($expected, Registry::get("key"));
		$val = array('me1', "me2", "me3");
		Registry::add("key", $val);
		array_push($expected, "me1", "me2", "me3");
		$this->assertIdentical($expected, Registry::get('key'));
		
		/* Test array add and get, and properly merging */
		$expected = array("one"=>1, "two"=>2);
		Registry::clear("key");
		Registry::add("key", array("one"=>1));
		Registry::add("key", array("two"=>2));
		Registry::add('key', 99);
		Registry::add('key', 100);
		array_push($expected, 99, 100);
		$this->assertIdentical($expected, Registry::get('key'));
		Registry::add("key", array("one"=>5));
		$expected["one"] = 5;
		$this->assertIdentical($expected, Registry::get('key'));
		Registry::add("key", array("two"=>6));
		$expected["two"] = 6;
		$this->assertIdentical($expected, Registry::get('key'));
		Registry::add("key", array("three"=>3));
		Registry::add("key", array("four"=>4));
		$expected["three"] = 3;
		$expected["four"] = 4;
		$this->assertIdentical($expected, Registry::get('key'));
		
		/* test overwritting */
		try {
			Registry::add("test-add-get-key", 5, true);
			Registry::add("test-add-get-key", 6);
			$this->fail("Readonly key should not be overwritten!");
		} catch (\VictoryCMS\Exception\OverwriteException $e) {}
		try {
			Registry::add("test-add-get-key-2", 5);
			Registry::add("test-add-get-key-2", 6);
			Registry::add("test-add-get-key-2", 7);
			Registry::add("test-add-get-key-2", 8, true);
			Registry::add("test-add-get-key-2", 9);
			$this->fail("Readonly key should not be overwritten!");
		} catch (\VictoryCMS\Exception\OverwriteException $e) {}
		
	}
	
	public function testAttachGet()
	{
		$obj = new RegistryNode("value");
		
		/* Test null binding and value */
		try {
			Registry::attach(null, $obj);
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* test bad boolean */
		try {
			Registry::attach("attach-key", $obj, array());
			$this->fail();
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		/* Test attach and get */
		Registry::attach('attach-key', $obj);
		$this->assertIdentical(Registry::get('attach-key'), $obj);
		Registry::clear('attach-key');
		
		$val = array('me1', "me2", "me3");
		Registry::attach("attach-key", $val);
		array_push($val, "me4", "me5", "me6");
		$this->assertIdentical($val, Registry::get('attach-key'));
		
		/* Test overwritting */
		try {
			$str = 'myStringValue';
			Registry::attach('test-set-get-key', $str);
			Registry::attach("test-set-get-key", new RegistryNode(null));
			$this->fail("Readonly key should not be overwritten!");
		} catch (\VictoryCMS\Exception\OverwriteException $e) {}
	}
	
	public function testIsKey()
	{
		$this->assertFalse(Registry::isKey(null));
		$this->assertFalse(Registry::isKey(false));
		$this->assertFalse(Registry::isKey('random-bad'));
		Registry::set('is-key', false);
		$this->assertTrue(Registry::isKey('is-key'));
		Registry::clear('is-key');
		$this->assertFalse(Registry::isKey('is-key'));
	}
	
	public function testIsReadOnly()
	{
		Registry::set('read-only', 1, true);
		$this->assertTrue(Registry::isReadOnly('read-only'));
		Registry::set('not-read-only', 2, false);
		$this->assertFalse(Registry::isReadOnly('not-read-only'));
		Registry::set('read-only-2', 1);
		$this->assertFalse(Registry::isReadOnly('read-only-2'));
		
		/* Test bad key's */
		try {
			Registry::isReadOnly("my-random-bad-key");
			$this->fail('expected unknown key to throw exception');
		} catch(\Exception $e) {}
		try {
			Registry::isReadOnly(null);
			$this->fail('expected null key to throw exception');
		} catch(\VictoryCMS\Exception\DataException $e) {}
	}
	
	public function testGet()
	{
		/* Test bad get key's */
		try {
			Registry::get("random-bad-key");
			$this->fail('expected unknown key to throw exception');
		} catch(\Exception $e) {}
		try {
			Registry::get(null);
			$this->fail('expected null key to throw exception');
		} catch(\Exception $e) {}
		
		/* Test singular get */
		Registry::clear("key");
		Registry::set("key", 5);
		$this->assertIdentical(5, Registry::get("key"));
		Registry::set("key", "string");
		$this->assertIdentical("string", Registry::get("key"));
		Registry::set('key', true);
		$this->assertIdentical(Registry::get('key'), true);
		
		/* Test multi-value get */
		$expected = array("one"=>1, "two"=>2, "three"=>3, "four"=>4);
		Registry::clear("key");
		Registry::set('key', array("one"=>1, "two"=>2, "three"=>3, "four"=>4));
		$this->assertIdentical($expected, Registry::get('key'));
		$val = array('me1', "me2", "me3");
		Registry::set("key", $val);
		$this->assertIdentical($val, Registry::get('key'));
		
	}
	
	public function testClear()
	{
		/* clear non-existant key */
		try {
			Registry::clear("random-bad-key");
			$this->fail('expected unknown key to throw exception');
		} catch(\Exception $e) {}
		
		/* clear null */
		try {
			Registry::clear(null);
			$this->fail('expected null key to throw exception');
		} catch(\Exception $e) {}
		
		/* regular clear */
		Registry::set("clear-key", 5);
		$this->assertTrue(Registry::clear("clear-key"));
		$this->assertFalse(Registry::isKey('clear-key'));
		try {
			Registry::get("clear-key");
			$this->fail("Key was not cleared!");
		} catch (\Exception $e) {}
		
		/* clear a read-only key-value binding */
		try {
			Registry::set("clear-key", 5, true);
			Registry::clear("clear-key");
			$this->fail("Clearing of read-only key!");
		} catch (\Exception $e) {}
		try {
			Registry::get("clear-key");
		} catch(\VictoryCMS\Exception\OverwriteException $e) {
			$this->fail("Clearing of read-only key!");
		}
	}
}