<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;

interface ConfigProvider
{
	/**
	* Return an array-based representation of this object to be used for parsing
	*
	* NOTE: if this method was named getConfig() it could interfere with magic getters from
	*       the Configurable trait
	*
	* @return array
	*/
	public function asConfig();
}