<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2012 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins;

use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Helpers\RegexpConvertor;

abstract class ConfiguratorBase implements ConfigProvider
{
	/**
	* @var Configurator
	*/
	protected $configurator;

	/**
	* @var integer Maximum amount of matches to process - used by the parser when running the global
	*              regexp
	*/
	protected $regexpLimit = 1000;

	/**
	* @var string  What to do if the number of matches exceeds the limit. Values can be: "ignore"
	*              (ignore matches past limit), "warn" (same as "ignore" but also log a warning) and
	*              "abort" (abort parsing)
	*/
	protected $regexpLimitAction = 'ignore';

	/**
	* @param Configurator $configurator
	* @param array        $overrideProps Properties of the plugin will be overwritten with those
	*/
	final public function __construct(Configurator $configurator, array $overrideProps = array())
	{
		foreach ($overrideProps as $k => $v)
		{
			$methodName = 'set' . ucfirst($k);

			if (method_exists($this, $methodName))
			{
				$this->$methodName($v);
			}
			elseif (property_exists($this, $k))
			{
				$this->$k = $v;
			}
			else
			{
				throw new RuntimeException("Unknown property '" . $k . "'");
			}
		}

		$this->configurator = $configurator;
		$this->setUp();
	}

	/**
	* Executed by constructor
	*/
	protected function setUp()
	{
	}

	/**
	* Return any extra XSL needed by this plugin
	*/
	public function getXSL()
	{
		return '';
	}

	/**
	* @return array|bool This plugin's config, or FALSE to disable this plugin
	*/
	public function asConfig()
	{
		$properties = get_object_vars($this);
		unset($properties['configurator']);

		return ConfigHelper::toArray($properties);
	}

	/**
	* Convert this plugin's config for use in the Javascript parser
	*
	* This is the base implementation. Plugins that require special modifications can override it
	*
	* @param  array $config Original config
	* @return array         The config used by the JS parser
	*/
	public function toJS(array $config)
	{
		if (isset($config['regexp']))
		{
			$regexps = array();

			foreach ((array) $config['regexp'] as $k => $regexp)
			{
				$regexps[$k] = RegexpConvertor::toJS($regexp);
			}

			$config['regexp'] = (is_array($config['regexp'])) ? $regexps : $regexps[0];
		}

		return $config;
	}

	//==========================================================================
	// Setters
	//==========================================================================

	/**
	* Set the maximum number of regexp matches
	*
	* @param  integer $limit
	* @return void
	*/
	public function setRegexpLimit($limit)
	{
		$limit = filter_var($limit, FILTER_VALIDATE_INT, array(
			'options' => array('min_range' => 1)
		));

		if (!$limit)
		{
			throw new InvalidArgumentException('regexpLimit must be a number greater than 0');
		}

		$this->regexpLimit = $limit;
	}

	/**
	* Set the action to perform when the regexp limit is broken
	*
	* @param  string $action
	* @return void
	*/
	public function setRegexpLimitAction($action)
	{
		if ($action !== 'ignore'
		 && $action !== 'warn'
		 && $action !== 'abort')
		{
			 throw new InvalidArgumentException("regexpLimitAction must be any of: 'ignore', 'warn' or 'abort'");
		}

		$this->regexpLimitAction = $action;
	}
}