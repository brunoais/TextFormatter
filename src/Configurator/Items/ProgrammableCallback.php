<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;

use InvalidArgumentException;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Items\Variant;
use s9e\TextFormatter\Configurator\JavaScript\Code;

class ProgrammableCallback implements ConfigProvider
{
	/**
	* @var callback Callback
	*/
	protected $callback;

	/**
	* @var Code JavaScript source code for this callback
	*/
	protected $js = null;

	/**
	* @var array List of params to be passed to the callback
	*/
	protected $params = [];

	/**
	* @var array Variables associated with this instance
	*/
	protected $vars = [];

	/**
	* @param callable $callback
	*/
	public function __construct($callback)
	{
		if (!is_callable($callback))
		{
			throw new InvalidArgumentException(__METHOD__ . '() expects a callback');
		}

		// Normalize ['foo', 'bar'] to 'foo::bar'
		if (is_array($callback) && is_string($callback[0]))
		{
			$callback = $callback[0] . '::' . $callback[1];
		}

		// Normalize '\\foo' to 'foo' and '\\foo::bar' to 'foo::bar'
		if (is_string($callback))
		{
			$callback = ltrim($callback, '\\');
		}

		$this->callback = $callback;
	}

	/**
	* Add a parameter by value
	*
	* @param  mixed $paramValue
	* @return self
	*/
	public function addParameterByValue($paramValue)
	{
		$this->params[] = $paramValue;

		return $this;
	}

	/**
	* Add a parameter by name
	*
	* The value will be dynamically generated by the caller
	*
	* @param  string $paramName
	* @return self
	*/
	public function addParameterByName($paramName)
	{
		$this->params[$paramName] = null;

		return $this;
	}

	/**
	* Get this object's callback
	*
	* @return callback
	*/
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	* Get this callback's JavaScript
	*
	* @return Code Instance of Code
	*/
	public function getJS()
	{
		// If no JavaScript was set but the callback looks like a PHP function, look into
		// ./../JavaScript/functions/ for a replacement
		if (!isset($this->js)
		 && is_string($this->callback)
		 && preg_match('#^[a-z_0-9]+$#D', $this->callback))
		{
			$filepath = __DIR__ . '/../JavaScript/functions/' . $this->callback . '.js';

			if (file_exists($filepath))
			{
				return new Code(file_get_contents($filepath));
			}
		}

		return $this->js;
	}

	/**
	* Get this object's variables
	*
	* @return array
	*/
	public function getVars()
	{
		return $this->vars;
	}

	/**
	* Remove all the parameters
	*
	* @return self
	*/
	public function resetParameters()
	{
		$this->params = [];

		return $this;
	}

	/**
	* Set this callback's JavaScript
	*
	* @param  Code|string $js JavaScript source code for this callback
	* @return self
	*/
	public function setJS($js)
	{
		if (!($js instanceof Code))
		{
			$js = new Code($js);
		}

		$this->js = $js;

		return $this;
	}

	/**
	* Set or overwrite one of this callback's variable
	*
	* @param  string $name  Variable name
	* @param  string $value Variable value
	* @return self
	*/
	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;

		return $this;
	}

	/**
	* Set all of this callback's variables at once
	*
	* @param  array $vars Associative array of values
	* @return self
	*/
	public function setVars(array $vars)
	{
		$this->vars = $vars;

		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function asConfig()
	{
		$config = ['callback' => $this->callback];

		foreach ($this->params as $k => $v)
		{
			if (is_numeric($k))
			{
				// By value
				$config['params'][] = $v;
			}
			elseif (isset($this->vars[$k]))
			{
				// By name, but the value is readily available in $this->vars
				$config['params'][] = $this->vars[$k];
			}
			else
			{
				// By name
				$config['params'][$k] = null;
			}
		}

		if (isset($config['params']))
		{
			$config['params'] = ConfigHelper::toArray($config['params'], true, true);
		}

		// Add the callback's JavaScript representation, if available
		$js = $this->getJS();
		if (isset($js))
		{
			$config['js'] = new Variant;
			$config['js']->set('JS', $js);
		}

		return $config;
	}
}