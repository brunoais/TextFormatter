<?php

namespace s9e\TextFormatter\Tests\Configurator\Javascript\Minifiers;

use s9e\TextFormatter\Configurator\Javascript\Minifiers\ClosureCompilerService;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\Javascript\Minifiers\ClosureCompilerService
*/
class ClosureCompilerServiceTest extends Test
{
	/**
	* @testdox
	* @group needs-network
	*/
	public function test()
	{
		$original =
			"function hello(name) {
				alert('Hello, ' + name);
			}
			hello('New user')";

		$expected = 'alert("Hello, New user");';

		$minifier = new ClosureCompilerService;
		$this->assertSame($expected, $minifier->minify($original));
	}
}