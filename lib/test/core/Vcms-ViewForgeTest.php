<?php
//
//  VictoryCMS - Content managment system and framework.
//
//  Copyright (C) 2010,2011	Lewis Gunsch <lgunsch@victorycms.org>
//  Copyright (C) 2010,2011	Mitchell Bosecke <mitchellbosecke@gmail.com>
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
use Vcms\FileUtils;
use Vcms\ViewForge;
use Vcms\Registry;

class ViewForgeTest extends UnitTestCase
{
	public function __construct()
	{
		parent::__construct('ViewForge Test');
	}

	public function testInstance()
	{
		$forge = ViewForge::getInstance();
		$this->assertIsA($forge, 'Vcms\ViewForge');
		$forge2 = $forge;
		$this->assertReference(
			$forge,
			$forge2,
			'Copy references are different'
		);
	}

	public function testClone()
	{
		$forge = ViewForge::getInstance();
		try {
			$forge2 = clone $forge;
			$this->fail('Did not throw an exception when cloning');
		} catch (Vcms\Exception\SingletonCopy $e) {}
	}

	public function testForge()
	{
		$forgeSpec = "
		{

			\"objects\":[
					{
						\"name\":\"TestView\",
						\"params\":{
							\"test1\":[\"obj1\",\"obj2\"],
							\"test2\":[\"obj3\",\"obj4\"]
						}
					}
			]
		}
		";

		$response = ViewForge::forge($forgeSpec);
		$this->assertIdentical($response->getStatusCode(), 200);
		$this->assertIdentical($response->getStatusMessage(), "OK");
		$this->assertIdentical($response->getContentType(), "text/html; charset=utf-8");
		$this->assertIdentical($response->getBody(), "12345");
	}

	public function testMultipleForge()
	{
		$forgeSpec = "
		{

			\"objects\":[
					{
						\"name\":\"TestView\",
						\"params\":{
							\"test1\":[\"obj1\",\"obj2\"],
							\"test2\":[\"obj3\",\"obj4\"]
						}
					},
					{
						\"name\":\"TestView2\",
						\"params\":{
							\"test1\":[\"obj1\",\"obj2\"],
							\"test2\":[\"obj3\",\"obj4\"]
						}
					}
			]
		}
		";

		$response = ViewForge::forge($forgeSpec);
		$this->assertIdentical($response->getStatusCode(), 200);
		$this->assertIdentical($response->getStatusMessage(), "OK");
		$this->assertIdentical($response->getContentType(), "text/html; charset=utf-8");
		$this->assertIdentical($response->getBody(), "12345678910");
	}

	public function testMalformedForgespec()
	{
		$forgeSpec = "
		{

			\"objects\":[
					{
						\"badname\":\"TestView\",
						\"params\":{
							\"test1\":[\"obj1\",\"obj2\"],
							\"test2\":[\"obj3\",\"obj4\"]
						}
					}
			]
		}
		";

		try{
			ViewForge::forge($forgeSpec);
			$this->fail('Did not throw an exception with a malformed forgeSpec');
		} catch(Exception $e){}
	}

	public function testMissingView()
	{
		$forgeSpec = "
		{

			\"objects\":[
					{
						\"name\":\"NonExistingView\",
						\"params\":{
							\"test1\":[\"obj1\",\"obj2\"],
							\"test2\":[\"obj3\",\"obj4\"]
						}
					}
			]
		}
		";

		$response = ViewForge::forge($forgeSpec);
		$this->assertIdentical($response->getStatusCode(), 404);
		$this->assertIdentical($response->getStatusMessage(), "Not Found");
		$this->assertIdentical($response->getContentType(), null);
		$this->assertIdentical($response->getBody(), null);
	}

	public function testDifferentMimeTypes()
	{
		$forgeSpec = "
			{

				\"objects\":[
						{
							\"name\":\"TestView\",
							\"params\":{
								\"test1\":[\"obj1\",\"obj2\"],
								\"test2\":[\"obj3\",\"obj4\"]
							}
						},
						{
							\"name\":\"TestView3\",
							\"params\":{
								\"test1\":[\"obj1\",\"obj2\"],
								\"test2\":[\"obj3\",\"obj4\"]
							}
						}
				]
			}
			";

		$response = ViewForge::forge($forgeSpec);
		$this->assertIdentical($response->getStatusCode(), 500);
		$this->assertIdentical($response->getStatusMessage(), "Internal Server Error");
		$this->assertIdentical($response->getContentType(), null);
		$this->assertIdentical($response->getBody(), null);
	}

	public function testForgeArray()
	{
		$forgeArray = array(
			"objects"=>array(
				array(
					"name"=>"TestView",
					"params"=>array(
						"test1"=>array("obj1", "obj2"),
						"test2"=>array("obj3", "obj4")
					)
				),
				array(
					"name"=>"TestView2",
					"params"=>array(
						"test1"=>array("obj1", "obj2"),
						"test2"=>array("obj3", "obj4")
					)
				)
			)
		);

		$response = ViewForge::forgeArray($forgeArray);
		$this->assertIdentical($response->getStatusCode(), 200);
		$this->assertIdentical($response->getStatusMessage(), "OK");
		$this->assertIdentical($response->getContentType(), "text/html; charset=utf-8");
		$this->assertIdentical($response->getBody(), "12345678910");
	}

	public function testForgeArrayBadJson()
	{
		// An invalid UTF8 sequence
		$forgeArray = array("\xB1\x31");

		try {
			$response = ViewForge::forgeArray($forgeArray);
			$this->fail('Did not throw an exception with a malformed forgeSpec Array');
		} catch(Exception $e){}
	}
}

