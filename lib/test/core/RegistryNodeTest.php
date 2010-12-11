<?php
//
//  VictoryCMS - Content managment system and framework.
//
//  Copyright (C) 2010  Andrew Crouse <amcrouse@victorycms.org>
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

class RegistryNodeTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct("Registry Node Test");
	}
	
	public function testInstance()
	{
		$node = new RegistryNode("value");
		$this->assertIsA($node, '\VictoryCMS\RegistryNode');
		
		$node2 = $node;
		$this->assertReference($node, $node2);
		
		try {
			$node = new RegistryNode("myval", null);
			$this->fail("Cannot set read-only to null.");
		} catch (\VictoryCMS\Exception\DataException $e) {}
		
		try {
			$node = new RegistryNode("myval", array());
			$this->fail("Cannot set read-only to not a bool value.");
		} catch (\VictoryCMS\Exception\DataException $e) {}
	}
	
	public function testSetGetValue()
	{
		$node = new RegistryNode("myval");
		$node->setValue("myval");
		$this->assertIdentical($node->getValue(), "myval");
		
		$node = new RegistryNode(array("my_array", 2, "three"=>3));
		$this->assertIdentical($node->getValue(), array("my_array", 2, "three"=>3));
		
		$obj = new RegistryNode('val');
		$node = new RegistryNode($obj);
		$this->assertIdentical($node->getValue(), $obj);
	}
	
	public function testReadOnly()
	{
		$node = new RegistryNode("myval");
		$node->setReadOnly();
		$this->assertTrue($node->isReadOnly());
		try {
			$node->setValue("myval");
			$this->fail("Should not be able to set value of readonly node.");
		} catch (\VictoryCMS\Exception\OverwriteException $e) {}
	}
}