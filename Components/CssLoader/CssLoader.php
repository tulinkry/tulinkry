<?php

namespace Tulinkry\Components;

use WebLoader,
	WebLoader\FileCollection,
	WebLoader\Filter,
	WebLoader\Compiler,
	Nette;

use Tulinkry;


interface ICssLoader
{
	/** @return CssLoader */
	public function create ();

}

class CssLoader extends WebLoader\Nette\CssLoader
{

	/**
	 * @param Nette\Application\Application
	 * @param Tulinkry\Services\ParameterService
	 * @param string
	 */
	public function __construct(Nette\Application\Application $application, Tulinkry\Services\ParameterService $pService,  $configPart = "css")
	{

		$basePath = $application->presenter->template->basePath;
		$files = new FileCollection(WWW_DIR . "/css");

		$filesArray = $pService->params["header"][$configPart];

		$remotes = [];
		foreach ( $filesArray as $key => $file )
		{
			if ( is_array ( $file ) )
			{
				if ( array_key_exists ( $key, $pService->params ) )
					foreach ( $filesArray [ $key ] as $x )
						if ( is_string ( $x ) )
							$filesArray [] = $pService->params [ $key ] . "/css/" . $x;
						else
							$filesArray [] = $x;
				unset ( $filesArray [ $key ] );
			}
			else if ( preg_match ( "/^http[s]{0,1}:\/\//", $file ) )
			{
				$remotes [ $key ] = $file;
				unset ( $filesArray [ $key ] );
			}
			else
			{
				$filesArray [] = $file;
				unset ( $filesArray [ $key ] );
			}
		}


		$files->addFiles($filesArray, "css");
		$files->addRemoteFiles($remotes);

		$compiler = Compiler::createCssCompiler($files, WWW_DIR . "/tmp");

		$compiler->addFileFilter(new Filter\LessFilter);
		$compiler->setJoinFiles(!$pService->params["debugMode"]);

		parent::__construct($compiler, $basePath . "/tmp");
	}

}