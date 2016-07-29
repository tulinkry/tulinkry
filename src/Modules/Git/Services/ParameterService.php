<?php

namespace Tulinkry\GitModule\Services;

use Nette;
use Tulinkry;

class ParameterService extends Nette\Object {

	/** @var array */
	public $params = array();

	public function __construct ($config = array ()) {
		$this->params = $config;
	}

	/**
	 * @param string
	 */
	public function &__get($name)
	{
		if ($name != "params" && isset($this->params[$name])) 
		{
			// avoid recursion
			return $this->params[$name];
		}
	}
	
};