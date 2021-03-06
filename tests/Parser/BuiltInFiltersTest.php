<?php

namespace s9e\TextFormatter\Tests\Parser;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Parser\BuiltInFilters;
use s9e\TextFormatter\Parser\FilterProcessing;
use s9e\TextFormatter\Parser\Logger;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Parser\BuiltInFilters
*/
class BuiltInFiltersTest extends Test
{
	protected static function getFilterTestdox($filterName, array $filterOptions, $original, $expected, $setup = null)
	{
		$testdox = '#' . $filterName;

		if ($filterOptions)
		{
			$testdox .= ' [';
			foreach ($filterOptions as $k => $v)
			{
				$testdox .= "'$k'=>" . var_export($v, true) . ',';
			}
			$testdox = substr($testdox, 0, -1) . ']';
		}

		if ($expected === false)
		{
			$testdox .= ' rejects ' . var_export($original, true);
		}
		elseif ($expected === $original)
		{
			$testdox .= ' accepts ' . var_export($original, true);
		}
		else
		{
			$testdox .= ' transforms ' . var_export($original, true)
					  . ' into ' . var_export($expected, true);
		}

		if (isset($setup))
		{
			$testdox .= ' with the appropriate configuration';
		}

		return $testdox;
	}

	/**
	* @dataProvider getRegressionsData
	* @testdox Regression tests
	*/
	public function testRegressions($original, array $results)
	{
		foreach ($results as $filterName => $expected)
		{
			$methodName = 'filter' . ucfirst($filterName);
			$testdox = self::getFilterTestdox($filterName, array(), $original, $expected);

			$this->assertSame(
				$expected,
				BuiltInFilters::$methodName($original),
				'Failed asserting that ' . $testdox
			);
		}
	}

	/**
	* @testdox Filters work
	* @dataProvider getData
	*/
	public function test($filterName, $original, $expected, array $filterOptions = array(), array $logs = array(), $setup = null)
	{
		$testdox = self::getFilterTestdox($filterName, $filterOptions, $original, $expected, $setup);

		$logger = new Logger;

		$this->assertSame(
			$expected,
			Hax::filterValue($original, $filterName, $filterOptions, $logger, $setup),
			'Failed asserting that ' . $testdox
		);

		$this->assertSame($logs, $logger->get(), "Logs don't match");
	}

	public function getData()
	{
		return array(
			array('alnum', '', false),
			array('alnum', 'abcDEF', 'abcDEF'),
			array('alnum', 'abc_def', false),
			array('alnum', '0123', '0123'),
			array('alnum', 'é', false),
			array('range', '2', 2, array('min' => 2, 'max' => 5)),
			array('range', '5', 5, array('min' => 2, 'max' => 5)),
			array('range', '-5', -5, array('min' => -5, 'max' => 5)),
			array('range', '1', 2, array('min' => 2, 'max' => 5), array(
				array('warn', 'Value outside of range, adjusted up to min value', array(
					'attrValue' => 1, 'min' => 2, 'max' => 5
				))
			)),
			array('range', '10', 5, array('min' => 2, 'max' => 5), array(
				array('warn', 'Value outside of range, adjusted down to max value', array(
					'attrValue' => 10, 'min' => 2, 'max' => 5
				))
			)),
			array('range', '5x', false, array('min' => 2, 'max' => 5)),
			array('url', 'http://www.älypää.com', 'http://www.xn--lyp-plada.com'),
			array(
				'url',
				'http://en.wikipedia.org/wiki/Matti_Nykänen', 'http://en.wikipedia.org/wiki/Matti_Nyk%C3%A4nen'
			),
			array(
				'url',
				'http://user:pass@en.wikipedia.org:80/wiki/Matti_Nykänen?foo&bar#baz', 'http://user:pass@en.wikipedia.org:80/wiki/Matti_Nyk%C3%A4nen?foo&bar#baz'
			),
			array(
				'url',
				'http://älypää.com:älypää.com@älypää.com',
				'http://%C3%A4lyp%C3%A4%C3%A4.com:%C3%A4lyp%C3%A4%C3%A4.com@xn--lyp-plada.com'
			),
			array('url', 'javascript:alert()', false),
			array('url', 'http://www.example.com', 'http://www.example.com'),
			array('url', '//www.example.com', '//www.example.com'),
			array(
				'url',
				'//www.example.com',
				false,
				array(),
				array(),
				function ($configurator)
				{
					$configurator->urlConfig->requireScheme();
				}
			),
			array('url', 'HTTP://www.example.com', 'http://www.example.com'),
			array('url', ' http://www.example.com ', 'http://www.example.com'),
			array('url', "http://example.com/''", 'http://example.com/%27%27'),
			array('url', 'http://example.com/""', 'http://example.com/%22%22'),
			array('url', 'http://example.com/(', 'http://example.com/%28'),
			array('url', 'http://example.com/)', 'http://example.com/%29'),
			array(
				'url',
				'ftp://example.com',
				false,
				array(),
				array(
					array(
						'err',
						'URL scheme is not allowed',
						array(
							'attrValue' => 'ftp://example.com',
							'scheme'    => 'ftp'
						)
					)
				)
			),
			array(
				'url',
				'ftp://example.com',
				'ftp://example.com',
				array(),
				array(),
				function ($configurator)
				{
					$configurator->urlConfig->allowScheme('ftp');
				}
			),
			array(
				'url',
				'http://evil.example.com',
				false,
				array(),
				array(
					array(
						'err',
						'URL host is not allowed',
						array(
							'attrValue' => 'http://evil.example.com',
							'host'      => 'evil.example.com'
						)
					)
				),
				function ($configurator)
				{
					// This is a paypal homograph
					$configurator->urlConfig->disallowHost('evil.example.com');
				}
			),
			array(
				'url',
				'http://www.pаypal.com',
				false,
				array(),
				array(
					array(
						'err',
						'URL host is not allowed',
						array(
							'attrValue' => 'http://www.xn--pypal-4ve.com',
							'host'      => 'www.xn--pypal-4ve.com'
						)
					)
				),
				function ($configurator)
				{
					// This is a paypal homograph
					$configurator->urlConfig->disallowHost('pаypal.com');
				}
			),
			array(
				'url',
				'http://t.co/gksG6xlq',
				'http://twitter.com/',
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://t.co/gksG6xlq',
							'to'   => 'http://twitter.com/'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('t.co');
					Hax::fakeRedirect('http://t.co/gksG6xlq', 'http://twitter.com/');
				}
			),
			array(
				'url',
				'http://bit.ly/go',
				'http://bit.ly/',
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://bit.ly/go',
							'to'   => 'http://bit.ly/2lkCBm'
						)
					),
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://bit.ly/2lkCBm',
							'to'   => 'http://bit.ly/'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('bit.ly');
					Hax::fakeRedirect('http://bit.ly/go',     'http://bit.ly/2lkCBm');
					Hax::fakeRedirect('http://bit.ly/2lkCBm', 'http://bit.ly/');
				}
			),
			array(
				'url',
				'http://bit.ly/2lkCBm',
				false,
				array(),
				array(
					array(
						'err',
						'Could not resolve redirect',
						array(
							'attrValue' => 'http://bit.ly/2lkCBm'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('bit.ly');
					Hax::fakeRedirect('http://bit.ly/2lkCBm', false);
				}
			),
			array(
				'url',
				'http://bit.ly/2lkCBm',
				false,
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://bit.ly/2lkCBm',
							'to'   => 'http://bit.ly/2lkCBm'
						)
					),
					array(
						'err',
						'Infinite recursion detected while following redirects',
						array(
							'attrValue' => 'http://bit.ly/2lkCBm'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('bit.ly');
					Hax::fakeRedirect('http://bit.ly/2lkCBm', 'http://bit.ly/2lkCBm');
				}
			),
			array(
				'url',
				'http://t.co/foo',
				false,
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://t.co/foo',
							'to'   => 'http://t.co/bar'
						)
					),
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://t.co/bar',
							'to'   => 'http://t.co/baz'
						)
					),
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://t.co/baz',
							'to'   => 'http://t.co/foo'
						)
					),
					array(
						'err',
						'Infinite recursion detected while following redirects',
						array(
							'attrValue' => 'http://t.co/foo'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('t.co');
					Hax::fakeRedirect('http://t.co/foo', 'http://t.co/bar');
					Hax::fakeRedirect('http://t.co/bar', 'http://t.co/baz');
					Hax::fakeRedirect('http://t.co/baz', 'http://t.co/foo');
				}
			),
			array(
				'url',
				'http://redirect.tld',
				false,
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://redirect.tld',
							'to'   => 'http://evil.tld'
						)
					),
					array(
						'err',
						'URL host is not allowed',
						array(
							'attrValue' => 'http://evil.tld',
							'host'      => 'evil.tld'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->disallowHost('evil.tld');
					$configurator->urlConfig->resolveRedirectsFrom('redirect.tld');
					Hax::fakeRedirect('http://redirect.tld', 'http://evil.tld');
				}
			),
			array(
				'url',
				'//t.co/gksG6xlq',
				'http://twitter.com/',
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://t.co/gksG6xlq',
							'to'   => 'http://twitter.com/'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('t.co');
					$configurator->urlConfig->setDefaultScheme('http');
					Hax::fakeRedirect('http://t.co/gksG6xlq', 'http://twitter.com/');
				}
			),
			array(
				'url',
				'http://js.tld',
				false,
				array(),
				array(
					array(
						'debug',
						'Resolved redirect',
						array(
							'from' => 'http://js.tld',
							'to'   => 'javascript:alert'
						)
					)
				),
				function ($configurator)
				{
					$configurator->urlConfig->resolveRedirectsFrom('js.tld');
					Hax::fakeRedirect('http://js.tld', 'javascript:alert');
				}
			),
			array('identifier', '123abcABC', '123abcABC'),
			array('identifier', '-_-', '-_-'),
			array('identifier', 'a b', false),
			array('color', '#123abc', '#123abc'),
			array('color', 'red', 'red'),
			array('color', '#1234567', false),
			array('color', 'blue()', false),
			array(
				'simpletext',
				'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-+.,_ ', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-+.,_ '
			),
			array('simpletext', 'a()b', false),
			array('simpletext', 'a[]b', false),
			array('regexp', 'ABC', 'ABC', array('regexp' => '/^[A-Z]+$/D')),
			array('regexp', 'Abc', false, array('regexp' => '/^[A-Z]+$/D')),
			array('email', 'example@example.com', 'example@example.com'),
			array('email', 'example@example.com()', false),
			array('map', 'dos', 'two', array(
				'map' => array(
					array('/^uno$/', 'one'),
					array('/^dos$/', 'two')
				)
			)),
			array('map', 'three', 'three', array(
				'map' => array(
					array('/^uno$/', 'one'),
					array('/^dos$/', 'two')
				)
			)),
			array('map', 'three', false, array(
				'map' => array(
					array('/^uno$/', 'one'),
					array('/^dos$/', 'two'),
					array('//',      false)
				)
			)),
			array('ip', '8.8.8.8', '8.8.8.8'),
			array('ip', 'ff02::1', 'ff02::1'),
			array('ip', 'localhost', false),
			array('ipv4', '8.8.8.8', '8.8.8.8'),
			array('ipv4', 'ff02::1', false),
			array('ipv4', 'localhost', false),
			array('ipv6', '8.8.8.8', false),
			array('ipv6', 'ff02::1', 'ff02::1'),
			array('ipv6', 'localhost', false),
			array('ipport', '8.8.8.8:80', '8.8.8.8:80'),
			array('ipport', '[ff02::1]:80', '[ff02::1]:80'),
			array('ipport', 'localhost:80', false),
			array('ipport', '[localhost]:80', false),
			array('ipport', '8.8.8.8', false),
			array('ipport', 'ff02::1', false),
		);
	}

	/**
	* NOTE: this test is not normative. Some cases exist solely to track regressions or changes in
	*       behaviour in ext/filter
	*/
	public function getRegressionsData()
	{
		return array(
			array('123', array('int' => 123, 'uint' => 123, 'float' => 123.0, 'number' => '123')),
			array('123abc', array('int' => false, 'uint' => false, 'float' => false, 'number' => false)),
			array('0123', array('int' => false, 'uint' => false, 'float' => 123.0, 'number' => '0123')),
			array('-123', array('int' => -123, 'uint' => false, 'float' => -123.0, 'number' => false)),
			array('12.3', array('int' => false, 'uint' => false, 'float' => 12.3, 'number' => false)),
			array('10000000000000000000', array('int' => false, 'uint' => false, 'float' => 10000000000000000000, 'number' => '10000000000000000000')),
			array('12e3', array('int' => false, 'uint' => false, 'float' => 12000.0, 'number' => false)),
			array('-12e3', array('int' => false, 'uint' => false, 'float' => -12000.0, 'number' => false)),
			array('12e-3', array('int' => false, 'uint' => false, 'float' => 0.012, 'number' => false)),
			array('-12e-3', array('int' => false, 'uint' => false, 'float' => -0.012, 'number' => false)),
			array('0x123', array('int' => false, 'uint' => false, 'float' => false, 'number' => false)),
		);
	}
}

class Hax
{
	use FilterProcessing;

	static protected $redirectTo = array();

	public static function filterValue($attrValue, $filterName, array $filterOptions, Logger $logger, $setup = null)
	{
		$configurator = new Configurator;
		$configurator
			->tags->add('FOO')
			->attributes->add('foo')
			->filterChain->append('#' . $filterName, $filterOptions);

		if (isset($setup))
		{
			$setup($configurator);
		}

		$config = $configurator->asConfig();
		ConfigHelper::filterVariants($config);

		if (self::$redirectTo)
		{
			stream_wrapper_unregister('http');
			stream_wrapper_register('http', __CLASS__);
		}

		try
		{
			$attrValue = self::executeFilter(
				$config['tags']['FOO']['attributes']['foo']['filterChain'][0],
				array(
					'attrName'       => 'foo',
					'attrValue'      => $attrValue,
					'logger'         => $logger,
					'registeredVars' => $config['registeredVars']
				)
			);
		}
		catch (Exception $e)
		{
		}

		if (self::$redirectTo)
		{
			self::$redirectTo = array();
			stream_wrapper_restore('http');
		}

		if (isset($e))
		{
			throw $e;
		}

		return $attrValue;
	}

	public static function fakeRedirect($from, $to)
	{
		self::$redirectTo[$from] = $to;
	}

	public function stream_open($url)
	{
		if (isset(self::$redirectTo[$url]))
		{
			if (self::$redirectTo[$url] === false)
			{
				return false;
			}

			$this->{'0'} = 'Location: ' . self::$redirectTo[$url];
		}

		return true;
	}

	public function stream_stat()
	{
		return false;
	}

	public function stream_read()
	{
		return '';
	}

	public function stream_eof()
	{
		return true;
	}
}