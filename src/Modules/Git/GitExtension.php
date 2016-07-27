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

        $builder->getDefinition($builder->getByType('Nette\Application\IRouter') ?: 'router')
                    ->addSetup('\Tulinkry\DI\GitExtension::modifyRouter(?)', [ '@self' ]);
        
        $builder->getDefinition($builder->getByType('Nette\Application\IPresenterFactory') ?: 'nette.presenterFactory')
                    ->addSetup('setMapping', array( 
                        array( 'Git' => 'Tulinkry\GitModule\*Controller' ) // nette 2.4 autoloads presenters and autowires them
                      ) 
        );
    }

    public static function modifyRouter(Nette\Application\IRouter &$router)
    {
        if (!$router instanceof RouteList) {
            throw new Nette\Utils\AssertionException('Your router should be an instance of Nette\Application\Routers\RouteList');
        }

        $router[] = $newRouter = new Route("git", array('module' => 'Git', 
                                           'presenter' => "Git",
                                           'action' => 'default'));

        $lastKey = count($router) - 1;
        foreach ($router as $i => $route) {
            if ($i === $lastKey) {
                break;
            }
            $router[$i + 1] = $route;
        }

        $router[0] = $newRouter;
    }
}
