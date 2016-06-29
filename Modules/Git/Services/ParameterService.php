<?php

namespace Tulinkry\GitModule\Services;

use Nette;
use Tulinkry;

class ParameterService extends Tulinkry\Services\ParameterService {

	public function __construct ($config = array ()) {
		$this->params = $config;
	}
};