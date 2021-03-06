<?php

namespace s9e\TextFormatter\Tests\Plugins\HTMLElements;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\Plugins\HTMLElements\Configurator;

/**
* @covers s9e\TextFormatter\Plugins\HTMLElements\Configurator
*/
class ConfiguratorTest extends Test
{
	/**
	* @testdox allowElement('b') creates a tag named 'html:b'
	*/
	public function testCreatesTags()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');

		$this->assertTrue($this->configurator->tags->exists('html:b'));
	}

	/**
	* @testdox allowElement('B') creates a tag named 'html:b'
	*/
	public function testCreatesTagsCaseInsensitive()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('B');

		$this->assertTrue($this->configurator->tags->exists('html:b'));
	}

	/**
	* @testdox allowElement() returns an instance of Tag
	*/
	public function testAllowElementInstance()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Tag',
			$plugin->allowElement('b')
		);
	}

	/**
	* @testdox The prefix can be customized at loading time through the 'prefix' property
	*/
	public function testCustomPrefix()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements', array('prefix' => 'xyz'));
		$plugin->allowElement('b');

		$this->assertTrue($this->configurator->tags->exists('xyz:b'));
	}

	/**
	* @testdox allowElement('script') throws an exception
	* @expectedException RuntimeException unsafe
	*/
	public function testUnsafeElement()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('script');
	}

	/**
	* @testdox allowUnsafeElement('script') allows the 'script' element
	*/
	public function testUnsafeElementAllowed()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowUnsafeElement('script');

		$this->assertTrue($this->configurator->tags->exists('html:script'));
	}

	/**
	* @testdox allowUnsafeElement() returns an instance of Tag
	*/
	public function testAllowUnsafeElementInstance()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Tag',
			$plugin->allowUnsafeElement('script')
		);
	}

	/**
	* @testdox allowAttribute('b', 'title') creates an attribute 'title' on tag 'html:b'
	*/
	public function testCreatesAttributes()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');
		$plugin->allowAttribute('b', 'title');

		$this->assertTrue($this->configurator->tags['html:b']->attributes->exists('title'));
	}

	/**
	* @testdox allowAttribute() returns an instance of Attribute
	*/
	public function testAllowAttributeInstance()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Attribute',
			$plugin->allowAttribute('b', 'title')
		);
	}

	/**
	* @testdox Attributes created by allowAttribute() are considered optional
	*/
	public function testCreatesOptionalAttributes()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');
		$plugin->allowAttribute('b', 'title');

		$this->assertFalse($this->configurator->tags['html:b']->attributes['title']->required);
	}

	/**
	* @testdox Attributes that are known to expect an URL are created with the '#url' filter
	*/
	public function testFilter()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('a');
		$plugin->allowAttribute('a', 'href');

		$this->assertTrue($this->configurator->tags['html:a']->attributes['href']->filterChain->contains('#url'));
	}

	/**
	* @testdox allowAttribute('b', 'title') throws an exception if 'b' was not explicitly allowed
	* @expectedException RuntimeException
	* @expectedExceptionMessage Element 'b' has not been allowed
	*/
	public function testAttributeOnUnknownElement()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowAttribute('b', 'title');
	}

	/**
	* @testdox allowAttribute('span', 'onmouseover') throws an exception
	* @expectedException RuntimeException unsafe
	*/
	public function testUnsafeAttribute()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('span');
		$plugin->allowAttribute('span', 'onmouseover');
	}

	/**
	* @testdox allowAttribute('span', 'style') throws an exception
	* @expectedException RuntimeException unsafe
	*/
	public function testUnsafeAttribute2()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('span');
		$plugin->allowAttribute('span', 'style');
	}

	/**
	* @testdox allowAttribute('span', 'onmouseover') allows the 'onmouseover' attribute on 'span' elements
	*/
	public function testUnsafeAttributeAllowed()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('span');
		$plugin->allowUnsafeAttribute('span', 'onmouseover');

		$this->assertTrue($this->configurator->tags['html:span']->attributes->exists('onmouseover'));
	}

	/**
	* @testdox allowUnsafeAttribute() returns an instance of Attribute
	*/
	public function testAllowUnsafeAttributeInstance()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');

		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Attribute',
			$plugin->allowUnsafeAttribute('b', 'onclick')
		);
	}

	/**
	* @testdox allowElement('*invalid*') throws an exception
	* @expectedException InvalidArgumentException invalid
	*/
	public function testInvalidElementName()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('*invalid*');
	}

	/**
	* @testdox allowAttribute('b', '*invalid*') throws an exception
	* @expectedException InvalidArgumentException invalid
	*/
	public function testInvalidAttributeName()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');
		$plugin->allowAttribute('b', '*invalid*');
	}

	/**
	* @testdox asConfig() returns FALSE if no elements were allowed
	*/
	public function testFalseConfig()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$this->assertFalse($plugin->asConfig());
	}

	/**
	* @testdox Has a quickMatch
	*/
	public function testConfigQuickMatch()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');

		$this->assertArrayHasKey(
			'quickMatch',
			$plugin->asConfig()
		);
	}

	/**
	* @testdox Generates a regexp for its config array
	*/
	public function testAsConfig()
	{
		$plugin = $this->configurator->plugins->load('HTMLElements');
		$plugin->allowElement('b');

		$this->assertArrayHasKey('regexp', $plugin->asConfig());
	}
}