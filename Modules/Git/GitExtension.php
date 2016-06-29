<?php

namespace Tulinkry\DI;

use Nette;
use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;

class GitExtension extends Nette\DI\CompilerExtension {

    public $defaults = array(
        "username" => "tulinkry",
        "repository" => null,
        "branch" => "master",
        "file" => "master.zip",
        "key" => null,
    );

    public function loadConfiguration() {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        if(!$config["username"]) {
            throw new Nette\InvalidArgumentException("GitExtension: username must be filled");
        }

        if(!$config["repository"]) {
            throw new Nette\InvalidArgumentException("GitExtension: repository must be filled");
        }

        if(!$config["file"]) {
            throw new Nette\InvalidArgumentException("GitExtension: file must be filled");
        }

        if(!$config["branch"]) {
            throw new Nette\InvalidArgumentException("GitExtension: branch must be filled");
        }

        $builder->addDefinition($this->prefix("parameters"))
                ->setClass("Tulinkry\GitModule\Services\ParameterService", [$config] );
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
