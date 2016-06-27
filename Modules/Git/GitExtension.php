<?php

namespace Tulinkry\DI;

use Nette;
use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;

class GitExtension extends Nette\DI\CompilerExtension {

    public $defaults = array();

    public function loadConfiguration() {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        $this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/git.neon'), $this->name);
    }

    public function beforeCompile() {
        $builder = $this->getContainerBuilder();
        $routerFactory = $builder->getDefinition('routerFactory');
        
        //$routerFactory->addSetup('registerRouterBeforeCreate', array( new Route("git", "Git:Git:default") ) );
        $routerFactory->addSetup('$service->onCreate[] = function ($router) '.
                '{ '.
                '   $router[] = ?; ' .
                '}', array(new Route("git", array('module' => 'Git', 
                                                  'presenter' => "Git",
                                                  'action' => 'default') )));
        
    	$builder->getDefinition('nette.presenterFactory')
                    ->addSetup('setMapping', array( 
                        array( 'Git' => 'Tulinkry\GitModule\*Presenter' ) 
                      ) 
                    );
    }

}
