<?php

namespace s9e\TextFormatter\Tests\Plugins\BBCodes\Configurator;

use s9e\TextFormatter\Plugins\BBCodes\Configurator\Repository;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\RepositoryCollection;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Plugins\BBCodes\Configurator\RepositoryCollection
*/
class RepositoryCollectionTest extends Test
{
	/**
	* @testdox Instances of Repository are added as-is
	*/
	public function testAddRepository()
	{
		$repository = new Repository(__DIR__ . '/../../../../src/s9e/TextFormatter/Plugins/BBCodes/Configurator/repository.xml');

		$collection = new RepositoryCollection;
		$collection->add('foo', $repository);

		$this->assertSame($repository, $collection->get('foo'));
	}

	/**
	* @testdox Anything else gets a new instance of Repository to be created
	*/
	public function testAddFilePath()
	{
		$collection = new RepositoryCollection;
		$collection->add('foo', __DIR__ . '/../../../../src/s9e/TextFormatter/Plugins/BBCodes/Configurator/repository.xml');

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Plugins\\BBCodes\\Configurator\\Repository', 
			$collection->get('foo')
		);
	}
}