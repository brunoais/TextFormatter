<?php

namespace s9e\TextFormatter\Tests\Parser;

use s9e\TextFormatter\Parser;
use s9e\TextFormatter\Parser\PluginsHandling;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Parser\PluginsHandling
*/
class PluginsHandlingTest extends Test
{
	/**
	* @testdox disablePlugin() disables given plugin
	*/
	public function testDisablePlugin()
	{
		$dummy = new PluginsHandlingDummy;
		$dummy->disablePlugin('Test');

		$this->assertTrue($dummy->pluginsConfig['Test']['isDisabled']);
	}

	/**
	* @testdox enablePlugin() re-enables a disabled plugin
	*/
	public function testEnablePlugin()
	{
		$dummy = new PluginsHandlingDummy;
		$dummy->disablePlugin('Test');
		$dummy->enablePlugin('Test');

		$this->assertTrue(empty($dummy->pluginsConfig['Test']['isDisabled']));
	}

	/**
	* @testdox Nothing happens if disablePlugin() is called for an inexistent plugin
	*/
	public function testDisableInexistentPlugin()
	{
		$dummy = new PluginsHandlingDummy;
		$dummy->disablePlugin('Unknown');

		$this->assertArrayNotHasKey('Unknown', $dummy->pluginsConfig);
	}

	/**
	* @testdox Nothing happens if enablePlugin() is called for an inexistent plugin
	*/
	public function testEnableInexistentPlugin()
	{
		$dummy = new PluginsHandlingDummy;
		$dummy->enablePlugin('Unknown');

		$this->assertArrayNotHasKey('Unknown', $dummy->pluginsConfig);
	}

	/**
	* @testdox executePluginParsers() executes plugins' parse() method
	*/
	public function testExecutePluginParsers()
	{
		$dummy  = new PluginsHandlingDummy;
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() does not execute disabled plugins
	*/
	public function testExecutePluginParsersDisabledPlugin()
	{
		$dummy  = new PluginsHandlingDummy;
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->never())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->disablePlugin('Test');

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() executes a plugin if its quickMatch test passes
	*/
	public function testExecutePluginParsersQuickMatchPass()
	{
		$dummy  = new PluginsHandlingDummy('[.....');
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['quickMatch'] = '[';

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() does not execute a plugin if its quickMatch test fails
	*/
	public function testExecutePluginParsersQuickMatchFail()
	{
		$dummy  = new PluginsHandlingDummy;
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->never())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['quickMatch'] = '[';

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() executes a plugin if its regexp test passes
	*/
	public function testExecutePluginParsersRegexpPass()
	{
		$dummy  = new PluginsHandlingDummy('...foo...');
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/foo/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 1000;

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() does not execute a plugin if its regexp test fails
	*/
	public function testExecutePluginParsersRegexpFail()
	{
		$dummy  = new PluginsHandlingDummy;
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->never())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/foo/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 1000;

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() passes the text and the matches to the plugin's parser
	*/
	public function testExecutePluginParsersArguments()
	{
		$text = '...foo...';
		$matches = array(
			array(array('o', 4)),
			array(array('o', 5))
		);

		$dummy  = new PluginsHandlingDummy($text);
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse')
		       ->with($text, $matches);

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/o/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 1000;

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() does not execute a plugin and throws a RuntimeException if the number of matches exceeds regexpLimit and regexpLimitAction is 'abort'
	* @expectedException RuntimeException
	* @expectedExceptionMessage Test limit exceeded
	*/
	public function testExecutePluginParsersRegexpLimitActionAbort()
	{
		$dummy  = new PluginsHandlingDummy('...foo...');
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->never())
		       ->method('parse');

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/o/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 1;
		$dummy->pluginsConfig['Test']['regexpLimitAction'] = 'abort';

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() executes a plugin with the first regexpLimit number of matches and logs a warning if the number of matches exceeds regexpLimit and regexpLimitAction is neither 'abort' or 'ignore'
	*/
	public function testExecutePluginParsersRegexpLimitActionWarn()
	{
		$text = '...fooo...';
		$matches = array(
			array(array('o', 4)),
			array(array('o', 5))
		);

		$dummy  = new PluginsHandlingDummy($text);
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse')
		       ->with($text, $matches);

		$logger = $this->getMock(
			's9e\\TextFormatter\\Parser\\Logger',
			array('warn')
		);
		$logger->expects($this->once())
		       ->method('warn')
		       ->with('Regexp limit exceeded. Only the allowed number of matches will be processed', array('pluginName' => 'Test', 'limit' => 2));
		$dummy->logger = $logger;

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/o/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 2;
		$dummy->pluginsConfig['Test']['regexpLimitAction'] = 'warn';

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() executes a plugin with the first regexpLimit number of matches if the number of matches exceeds regexpLimit and regexpLimitAction is neither 'ignore'
	*/
	public function testExecutePluginParsersRegexpLimitActionIgnore()
	{
		$text = '...fooo...';
		$matches = array(
			array(array('o', 4)),
			array(array('o', 5))
		);

		$dummy  = new PluginsHandlingDummy($text);
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);
		$plugin->expects($this->once())
		       ->method('parse')
		       ->with($text, $matches);

		$dummy->pluginParsers['Test'] = array($plugin, 'parse');
		$dummy->pluginsConfig['Test']['regexp'] = '/o/';
		$dummy->pluginsConfig['Test']['regexpLimit'] = 2;
		$dummy->pluginsConfig['Test']['regexpLimitAction'] = 'ignore';

		$dummy->executePluginParsers();
	}

	/**
	* @testdox executePluginParsers() creates an instance of the class name stored in className if present
	*/
	public function testExecutePluginParsersCustomClass()
	{
		$dummy  = new PluginsHandlingDummy('...foo...');
		$plugin = $this->getMock(
			's9e\\TextFormatter\\Plugins\\ParserBase',
			array('parse'),
			array($dummy, array())
		);

		$className = get_class($plugin);
		$dummy->pluginsConfig['Test']['className'] = $className;

		$dummy->executePluginParsers();

		$this->assertArrayHasKey('Test', $dummy->pluginParsers);
		$this->assertArrayHasKey(0, $dummy->pluginParsers['Test']);
		$this->assertInstanceOf($className, $dummy->pluginParsers['Test'][0]);
	}

	/**
	* @testdox registerParser() can register a callback that replaces the parser of an existing plugin
	*/
	public function testRegisterParserExisting()
	{
		$dummy  = new PluginsHandlingDummy;
		$parser = $this->getMock('stdClass', array('foo'));
		$parser->expects($this->once())
		       ->method('foo');

		$dummy->registerParser('Test', array($parser, 'foo'));

		$dummy->executePluginParsers();
	}

	/**
	* @testdox registerParser() can register a callback that acts as the parser of a new plugin
	*/
	public function testRegisterParserNew()
	{
		$dummy  = new PluginsHandlingDummy;
		$dummy->pluginsConfig = array();

		$parser = $this->getMock('stdClass', array('foo'));
		$parser->expects($this->once())
		       ->method('foo');

		$dummy->registerParser('Foo', array($parser, 'foo'));

		$dummy->executePluginParsers();
	}

	/**
	* @testdox registerParser() throws an exception if its second argument is not callable
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage must be a valid callback
	*/
	public function testRegisterParserInvalid()
	{
		$dummy  = new PluginsHandlingDummy;
		$dummy->registerParser('Foo', '*invalid*');
	}
}

class PluginsHandlingDummy extends Parser
{
	public $logger;
	public $pluginParsers = array();
	public $pluginsConfig = array(
		'Test' => array(
		)
	);

	public function __construct($text = '')
	{
		$this->text = $text;
	}

	public function executePluginParsers()
	{
		return call_user_func_array('parent::executePluginParsers', func_get_args());
	}
}