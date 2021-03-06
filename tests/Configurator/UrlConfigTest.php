<?php

namespace s9e\TextFormatter\Tests\Configurator;

use s9e\TextFormatter\Configurator\UrlConfig;
use s9e\TextFormatter\Configurator\Items\Variant;
use s9e\TextFormatter\Configurator\Javascript\RegExp;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\UrlConfig
*/
class UrlConfigTest extends Test
{
	public function setUp()
	{
		$this->urlConfig = new UrlConfig;
	}

	/**
	* @testdox asConfig() creates a Javascript variant for allowedSchemes
	*/
	public function testAsConfigAllowedSchemesVariant()
	{
		$this->urlConfig->allowScheme('http');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Variant',
			$urlConfig['allowedSchemes']
		);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Javascript\\RegExp',
			$urlConfig['allowedSchemes']->get('Javascript')
		);
	}

	/**
	* @testdox asConfig() creates a Javascript variant for disallowedHosts
	*/
	public function testAsConfigDisallowedHostsVariant()
	{
		$this->urlConfig->disallowHost('pаypal.com');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('disallowedHosts', $urlConfig);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Variant',
			$urlConfig['disallowedHosts']
		);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Javascript\\RegExp',
			$urlConfig['disallowedHosts']->get('Javascript')
		);
	}

	/**
	* @testdox asConfig() creates a Javascript variant for resolveRedirectsHosts
	*/
	public function testAsConfigResolveRedirectsHostsVariant()
	{
		$this->urlConfig->resolveRedirectsFrom('t.co');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('resolveRedirectsHosts', $urlConfig);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Items\\Variant',
			$urlConfig['resolveRedirectsHosts']
		);
		$this->assertInstanceOf(
			's9e\\TextFormatter\\Configurator\\Javascript\\RegExp',
			$urlConfig['resolveRedirectsHosts']->get('Javascript')
		);
	}

	/**
	* @testdox Disallowed IDNs are punycoded
	*/
	public function testDisallowedIDNsArePunycoded()
	{
		$this->urlConfig->disallowHost('pаypal.com');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('disallowedHosts', $urlConfig);
		$this->assertContains('xn--pypal-4ve\\.com', $urlConfig['disallowedHosts']->get());
	}

	/**
	* @testdox disallowHost('example.org') disallows "example.org"
	*/
	public function testDisallowHost()
	{
		$this->urlConfig->disallowHost('example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'example.org');
	}

	/**
	* @testdox disallowHost('example.org') disallows "EXAMPLE.ORG"
	*/
	public function testDisallowHostCaseInsensitive()
	{
		$this->urlConfig->disallowHost('example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'EXAMPLE.ORG');
	}

	/**
	* @testdox disallowHost('example.org') disallows "www.example.org"
	*/
	public function testDisallowHostSubdomains()
	{
		$this->urlConfig->disallowHost('example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'www.example.org');
	}

	/**
	* @testdox disallowHost('example.org') does not disallow "myexample.org"
	*/
	public function testDisallowHostSubdomainsNoPartialMatch()
	{
		$this->urlConfig->disallowHost('example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertNotRegexp($urlConfig['disallowedHosts']->get(), 'myexample.org');
	}

	/**
	* @testdox disallowHost('example.org', false) does not disallow "www.example.org"
	*/
	public function testDisallowHostNoSubdomains()
	{
		$this->urlConfig->disallowHost('example.org', false);
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertNotRegexp($urlConfig['disallowedHosts']->get(), 'www.example.org');
	}

	/**
	* @testdox disallowHost('*.example.org') disallows "www.example.org"
	*/
	public function testDisallowHostWithWildcard()
	{
		$this->urlConfig->disallowHost('*.example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'www.example.org');
	}

	/**
	* @testdox disallowHost('*.example.org') disallows "www.xxx.example.org"
	*/
	public function testDisallowHostWithWildcard2()
	{
		$this->urlConfig->disallowHost('*.example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'www.xxx.example.org');
	}

	/**
	* @testdox disallowHost('*.example.org') does not disallow "example.org"
	*/
	public function testDisallowHostWithWildcard3()
	{
		$this->urlConfig->disallowHost('*.example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertNotRegexp($urlConfig['disallowedHosts']->get(), 'example.org');
	}

	/**
	* @testdox disallowHost('*.example.org') does not disallow "example.org.org"
	*/
	public function testDisallowHostWithWildcard4()
	{
		$this->urlConfig->disallowHost('*.example.org');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertNotRegexp($urlConfig['disallowedHosts']->get(), 'example.org.org');
	}

	/**
	* @testdox disallowHost('*xxx*') disallows "xxx.com"
	*/
	public function testDisallowHostWithWildcard5()
	{
		$this->urlConfig->disallowHost('*xxx*');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'xxx.com');
	}

	/**
	* @testdox disallowHost('*xxx*') disallows "foo.xxx"
	*/
	public function testDisallowHostWithWildcard6()
	{
		$this->urlConfig->disallowHost('*xxx*');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'foo.xxx');
	}

	/**
	* @testdox disallowHost('*xxx*') disallows "myxxxsite.com"
	*/
	public function testDisallowHostWithWildcard7()
	{
		$this->urlConfig->disallowHost('*xxx*');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertRegexp($urlConfig['disallowedHosts']->get(), 'myxxxsite.com');
	}

	/**
	* @testdox resolveRedirectsFrom('bit.ly') matches "bit.ly"
	*/
	public function testResolveRedirects()
	{
		$this->urlConfig->resolveRedirectsFrom('bit.ly');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('resolveRedirectsHosts', $urlConfig);
		$this->assertRegexp($urlConfig['resolveRedirectsHosts']->get(), 'bit.ly');
	}

	/**
	* @testdox resolveRedirectsFrom('bit.ly') matches "foo.bit.ly"
	*/
	public function testResolveRedirectsSubdomains()
	{
		$this->urlConfig->resolveRedirectsFrom('bit.ly');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('resolveRedirectsHosts', $urlConfig);
		$this->assertRegexp($urlConfig['resolveRedirectsHosts']->get(), 'foo.bit.ly');
	}

	/**
	* @testdox resolveRedirectsFrom('bit.ly', false) does not match "foo.bit.ly"
	*/
	public function testResolveRedirectsNoSubdomains()
	{
		$this->urlConfig->resolveRedirectsFrom('bit.ly', false);
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('resolveRedirectsHosts', $urlConfig);
		$this->assertNotRegexp($urlConfig['resolveRedirectsHosts']->get(), 'foo.bit.ly');
	}

	/**
	* @testdox "http" is an allowed scheme by default
	*/
	public function testAllowSchemeHTTP()
	{
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertRegexp($urlConfig['allowedSchemes']->get(), 'http');
	}

	/**
	* @testdox "https" is an allowed scheme by default
	*/
	public function testAllowSchemeHTTPS()
	{
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertRegexp($urlConfig['allowedSchemes']->get(), 'https');
	}

	/**
	* @testdox "HTTPS" is an allowed scheme by default
	*/
	public function testAllowSchemeHTTPSCaseInsensitive()
	{
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertRegexp($urlConfig['allowedSchemes']->get(), 'HTTPS');
	}

	/**
	* @testdox "ftp" is an allowed scheme by default
	*/
	public function testDisallowedSchemeFTP()
	{
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertNotRegexp($urlConfig['allowedSchemes']->get(), 'ftp');
	}

	/**
	* @testdox getAllowedSchemes() returns an array containing all the allowed schemes
	*/
	public function testGetAllowedSchemes()
	{
		$this->assertEquals(
			array('http', 'https'),
			$this->urlConfig->getAllowedSchemes()
		);
	}

	/**
	* @testdox allowScheme('ftp') allows "ftp" as scheme
	*/
	public function testAllowSchemeFTP()
	{
		$this->urlConfig->allowScheme('ftp');
		$urlConfig = $this->urlConfig->asConfig();

		$this->assertArrayHasKey('allowedSchemes', $urlConfig);
		$this->assertRegexp($urlConfig['allowedSchemes']->get(), 'ftp');
	}

	/**
	* @testdox allowScheme('<invalid>') throws an exception
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid scheme name '<invalid>'
	*/
	public function testInvalidAllowScheme()
	{
		$this->urlConfig->allowScheme('<invalid>');
	}

	/**
	* @testdox There is no default scheme by default
	*/
	public function testNoDefaultScheme()
	{
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertArrayNotHasKey('defaultScheme', $urlConfig);
	}

	/**
	* @testdox setDefaultScheme('http') sets "http" as default scheme
	*/
	public function testSetDefaultScheme()
	{
		$this->urlConfig->setDefaultScheme('http');
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertArrayHasKey('defaultScheme', $urlConfig);
		$this->assertSame('http', $urlConfig['defaultScheme']);
	}

	/**
	* @testdox setDefaultScheme('<invalid>') throws an exception
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid scheme name '<invalid>'
	*/
	public function testInvalidDefaultScheme()
	{
		$this->urlConfig->setDefaultScheme('<invalid>');
	}

	/**
	* @testdox URLs do not require a scheme by default
	*/
	public function testNoRequiredScheme()
	{
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertArrayNotHasKey('requireScheme', $urlConfig);
	}

	/**
	* @testdox requireScheme() forces URLs to require a scheme
	*/
	public function testRequireScheme()
	{
		$this->urlConfig->requireScheme();
		$urlConfig = $this->urlConfig->asConfig();
		$this->assertArrayHasKey('requireScheme', $urlConfig);
		$this->assertTrue($urlConfig['requireScheme']);
	}

	/**
	* @testdox requireScheme('nonbool') throws an exception
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage requireScheme() expects a boolean
	*/
	public function testRequireSchemeInvalid()
	{
		$this->urlConfig->requireScheme('nonbool');
	}
}