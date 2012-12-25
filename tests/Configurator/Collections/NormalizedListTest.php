<?php

namespace s9e\TextFormatter\Tests\Configurator\Collections;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\Configurator\Collections\NormalizedList;

/**
* @covers s9e\TextFormatter\Configurator\Collections\NormalizedList
*/
class NormalizedListTest extends Test
{
	public $normalizedList;

	public function setUp()
	{
		$this->normalizedList = new NormalizedList;
	}

	/**
	* @testdox append() adds the value at the end of the list
	*/
	public function testAppend()
	{
		$this->normalizedList->append(1);
		$this->normalizedList->append(2);

		$this->assertSame(1, $this->normalizedList[0]);
		$this->assertSame(2, $this->normalizedList[1]);
	}

	/**
	* @testdox $normalizedList[] = 'foo' maps to $normalizedList->append('foo')
	*/
	public function testArrayAccessAppend()
	{
		$mock = $this->getMock(
			's9e\\TextFormatter\\Configurator\\Collections\\NormalizedList',
			array('append')
		);

		$mock->expects($this->once())
		     ->method('append')
		     ->with($this->equalTo('foo'));

		$mock[] = 'foo';
	}

	/**
	* @testdox prepend() adds the value at the beginning of the list
	*/
	public function testPrepend()
	{
		$this->normalizedList->prepend(1);
		$this->normalizedList->prepend(2);

		$this->assertSame(2, $this->normalizedList[0]);
		$this->assertSame(1, $this->normalizedList[1]);
	}

	/**
	* @testdox insert() inserts the value at given offset
	*/
	public function testInsert()
	{
		$this->normalizedList->append(1);
		$this->normalizedList->append(3);
		$this->normalizedList->insert(1, 2);

		$this->assertSame(1, $this->normalizedList[0]);
		$this->assertSame(2, $this->normalizedList[1]);
		$this->assertSame(3, $this->normalizedList[2]);
	}

	/**
	* @testdox insert() can insert value that is an array
	*/
	public function testInsertArray()
	{
		$this->normalizedList->append(1);
		$this->normalizedList->append(3);
		$this->normalizedList->insert(1, array('foo', 'bar'));

		$this->assertSame(1, $this->normalizedList[0]);
		$this->assertSame(array('foo', 'bar'), $this->normalizedList[1]);
		$this->assertSame(3, $this->normalizedList[2]);
	}

	/**
	* @testdox insert() throws an exception if the offset is out of bounds
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid offset '3'
	*/
	public function testInsertInvalid()
	{
		$this->normalizedList->insert(3, 1);
	}

	/**
	* @testdox $normalizedList[0] = 'foo' replaces the first value of the list if it exists
	*/
	public function testArrayAccessReplace()
	{
		$this->normalizedList->append('bar');
		$this->normalizedList[0] = 'foo';

		$this->assertSame(1, count($this->normalizedList));
		$this->assertFalse($this->normalizedList->contains('bar'));
		$this->assertTrue($this->normalizedList->contains('foo'));
	}

	/**
	* @testdox $normalizedList[0] = 'foo' appends to the list if it's empty
	*/
	public function testArrayAccessAddNew()
	{
		$this->normalizedList[0] = 'foo';

		$this->assertSame(1, count($this->normalizedList));
		$this->assertTrue($this->normalizedList->contains('foo'));
	}

	/**
	* @testdox $normalizedList[1] = 'foo' throws an InvalidArgumentException if the list is empty
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid offset '1'
	*/
	public function testArrayAccessInvalidSet()
	{
		$this->normalizedList[1] = 'foo';
	}

	/**
	* @testdox $normalizedList['foo'] = 'bar' throws an InvalidArgumentException
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid offset 'foo'
	*/
	public function testArrayAccessInvalidKey()
	{
		$this->normalizedList['foo'] = 'bar';
	}

	/**
	* @testdox Deleting a value by key reorders the list to remove gaps
	*/
	public function testDeleteReordersChain()
	{
		$this->normalizedList->append(1);
		$this->normalizedList->append(2);

		$this->normalizedList->delete(0);

		$this->assertSame(1, count($this->normalizedList));
		$this->assertSame(2, $this->normalizedList[0]);
	}
}