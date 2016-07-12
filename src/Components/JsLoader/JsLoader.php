<?php

namespace Tulinkry\Components;

use WebLoader,
	WebLoader\FileCollection,
	WebLoader\Filter,
	WebLoader\Compiler,
	Nette;
use Tulinkry;


interface IJsLoader
{
	/** @return JsLoader */
	public function create ();

}

class JsLoader extends WebLoader\Nette\JavaScriptLoader
{

	/**
	 * @param Nette\Application\Application
	 * @param Schmutzka\Services\ParamService
	 * @param string
	 */
	public function __construct(Nette\Application\Application $application, Tulinkry\Services\ParameterService $pService,  $configPart = "js")
	{
		$basePath = $application->presenter->template->basePath;
		$files = new FileCollection(WWW_DIR . "/js");


		$filesArray = $pService->params["header"][$configPart];
		$remotes = [];
		foreach ( $filesArray as $key => $file )
			if ( is_array ( $file ) )
			{
				if ( array_key_exists ( $key, $pService->params ) )
					foreach ( $filesArray [ $key ] as $x )
						if ( is_string ( $x ) )
						{
							if ( ! array_key_exists ( $key, $pService->params ) )
								throw new \Exception ( "Variable $key is missing in configuration and needed for loading css/js files" );
							$filesArray [] = $pService->params [ $key ] . "/js/" . $x;
						}
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
		$files->addFiles($filesArray, "js");
		$files->addRemoteFiles($remotes);

		$anotherArray = [];
		if ( array_key_exists ( "js-extensions", $pService->params ) )
		{
			foreach ( $pService->params["js-extensions"] as $ext )
			{
				if ( array_key_exists ( "path", $ext ) )
					$anotherArray [] = $ext [ "path" ];
			}
		}

		//if (array_key_exists ( "ajax-progress-bar", $pService->params ) && $pService->params["ajax-progress-bar"] )
		//	$anotherArray [] = $pService->params["assets"] . "/" . "js/" . "addons/" . "ajax-progress-bar.js";

		$files->addFiles($anotherArray, "js");

		$compiler = Compiler::createJsCompiler($files, WWW_DIR . "/tmp");

		if (!$pService->params["debugMode"]) { // production only
			/*$compiler->addFilter(function ($code) {
				return Filter\JSMin::minify($code);
			});*/

		} else {
			$compiler->setJoinFiles(FALSE);
		}

		parent::__construct($compiler, $basePath . "/tmp");
	}

}