<?php

namespace Tulinkry\Services;

use Nette;

class ParameterService extends Nette\Object
{
	/** @var array */
	public $params = array();


	/**
	 * @param Nette\DI\Container 
	 */
	public function __construct(Nette\DI\Container $context) 
	{ 
		$this->params = $context->parameters;
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
	
}