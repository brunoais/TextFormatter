<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;

use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Items\AttributeFilter;

class AttributeFilterCollection extends NormalizedCollection
{
	/**
	* Return a value from this collection
	*
	* @param  string $key
	* @return ProgrammableCallback
	*/
	public function get($key)
	{
		$key = $this->normalizeKey($key);

		if (!$this->exists($key))
		{
			if ($key[0] === '#')
			{
				$filterName = ucfirst(substr($key, 1));
				$className  = 's9e\\TextFormatter\\Configurator\\Items\\AttributeFilters\\' . $filterName;

				if (!class_exists($className))
				{
					throw new InvalidArgumentException("Unknown attribute filter '" . $key . "'");
				}

				$this->set($key, new $className);
			}
			else
			{
				$this->set($key, new AttributeFilter($key));
			}
		}

		// Get the filter from the collection
		$filter = parent::get($key);

		// Clone it to preserve the original instance
		$filter = clone $filter;

		return $filter;
	}

	/**
	* {@inheritdoc}
	*/
	public function normalizeKey($key)
	{
		// Built-in/custom filter, normalized to lowercase
		if (preg_match('/^#[a-z_0-9]+$/Di', $key))
		{
			return strtolower($key);
		}

		// Valid callback
		if (is_string($key) && is_callable($key))
		{
			return $key;
		}

		throw new InvalidArgumentException("Invalid filter name '" . $key . "'");
	}

	/**
	* {@inheritdoc}
	*/
	public function normalizeValue($value)
	{
		if (!($value instanceof AttributeFilter))
		{
			$value = new AttributeFilter($value);
		}

		return $value;
	}
}