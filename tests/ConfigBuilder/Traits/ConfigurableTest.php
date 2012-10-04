<?php

namespace s9e\TextFormatter\Tests\ConfigBuilder\Traits;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\ConfigBuilder\Collections\NormalizedCollection;
use s9e\TextFormatter\ConfigBuilder\Traits\Configurable;

/**
* @covers s9e\TextFormatter\ConfigBuilder\Traits\Configurable
*/
class ConfigurableTest extends Test
{
	/**
	* @testdox __get() throws a RuntimeException if the property does not exist
	* @expectedException RuntimeException
	* @expectedExceptionMessage Property 'foo' does not exist
	*/
	public function testMagicGetInexistent()
	{
		$dummy = new ConfigurableTestDummy;
		$dummy->foo;
	}

	/**
	* @testdox __get($k) returns the property if it exists
	*/
	public function testMagicGet()
	{
		$dummy = new ConfigurableTestDummy;

		$this->assertSame(42, $dummy->int);
	}

	/**
	* @testdox __get($k) returns null if the property is null
	*/
	public function testMagicGetNull()
	{
		$dummy = new ConfigurableTestDummy;

		$this->assertNull($dummy->null);
	}

	/**
	* @testdox __set('foo', 'bar') calls setFoo('bar') if it exists
	*/
	public function testMagicSetMethod()
	{
		$dummy = new ConfigurableTestDummy;
		$dummy->foo = 'bar';

		$this->assertSame('foobar', $dummy->foo);
	}

	/**
	* @testdox __set() can create new properties
	*/
	public function testMagicSetNew()
	{
		$dummy = new ConfigurableTestDummy;
		$dummy->inexistent = 'foo';

		$this->assertSame('foo', $dummy->inexistent);
	}

	/**
	* @testdox __set() preserves the PHP type of existing properties
	*/
	public function testMagicSetPreservesType()
	{
		$dummy = new ConfigurableTestDummy;
		$dummy->int = '55';

		$this->assertSame(55, $dummy->int);
	}

	/**
	* @testdox __set() will clear and repopulate a NormalizedCollection rather than overwrite it
	*/
	public function testMagicSetNormalizedCollection()
	{
		$dummy = new ConfigurableTestDummy;

		$dummy->collection->set('old', 'old');

		$values = array('foo' => 'bar', 'baz' => 'quux');
		$dummy->collection = $values;

		$this->assertInstanceOf(
			's9e\\TextFormatter\\ConfigBuilder\\Collections\\NormalizedCollection',
			$dummy->collection
		);

		$this->assertSame($values, iterator_to_array($dummy->collection));
	}
}

class ConfigurableTestDummy
{
	use Configurable;

	protected $int = 42;
	protected $null = null;
	protected $collection;

	public function __construct()
	{
		$this->collection = new NormalizedCollection;
	}

	protected function setFoo($str)
	{
		$this->foo = 'foo' . $str;
	}
}