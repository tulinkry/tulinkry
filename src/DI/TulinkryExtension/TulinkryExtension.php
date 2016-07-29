<?php

namespace Tulinkry\DI;

use Nette;
use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;

class TulinkryExtension extends Nette\DI\CompilerExtension {

    public $defaults = array(
    );

    public function loadConfiguration() {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        $this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);
    }

}