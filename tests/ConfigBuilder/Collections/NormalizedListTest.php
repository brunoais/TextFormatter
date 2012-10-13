<?php

namespace s9e\TextFormatter\Tests\ConfigBuilder\Collections;

use stdClass;
use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\ConfigBuilder\Collections\NormalizedList;
use s9e\TextFormatter\ConfigBuilder\Items\CallbackTemplate;
use s9e\TextFormatter\ConfigBuilder\Items\Filter;
use s9e\TextFormatter\ConfigBuilder\Items\FilterLink;

/**
* @covers s9e\TextFormatter\ConfigBuilder\Collections\NormalizedList
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
	* @testdox $normalizedList[] = 'foo' maps to $normalizedList->append('foo')
	*/
	public function testArrayAccessAppend()
	{
		$mock = $this->getMock(
			's9e\\TextFormatter\\ConfigBuilder\\Collections\\NormalizedList',
			array('append')
		);

		$mock->expects($this->once())
		     ->method('append')
		     ->with($this->equalTo('foo'));

		$mock[] = 'foo';
	}

	/**
	* @testdox contains() returns true if the given value is present in the list
	*/
	public function testPositiveContains()
	{
		$this->normalizedList->append(1);

		$this->assertTrue($this->normalizedList->contains(1));
	}

	/**
	* @testdox contains() returns false if the given value is not present in the list
	*/
	public function testNegativeContains()
	{
		$this->normalizedList->append(1);

		$this->assertFalse($this->normalizedList->contains(2));
	}

	/**
	* @testdox contains() checks for equality, not identity
	*/
	public function testEqualityContains()
	{
		$this->normalizedList->append(new stdClass);

		$this->assertTrue($this->normalizedList->contains(new stdClass));
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