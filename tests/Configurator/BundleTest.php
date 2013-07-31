<?php

namespace s9e\TextFormatter\Tests\Configurator;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Bundle;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\Bundle
*/
class BundleTest extends Test
{
	/**
	* @testdox getConfigurator() returns a configured instance of Configurator
	*/
	public function test()
	{
		$configurator = DummyBundle::getConfigurator();

		$this->assertInstanceOf('s9e\\TextFormatter\\Configurator', $configurator);
		$this->assertAttributeSame('bar', 'foo', $configurator);
	}
}

class DummyBundle extends Bundle
{
	public function configure(Configurator $configurator)
	{
		$configurator->foo = 'bar';
	}
}