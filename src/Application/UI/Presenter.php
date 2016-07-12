<?php

namespace Tulinkry\Application\UI;

use Nette;
use Nette\Http\Url;
use Nette\Reflection\Property;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;
use Tulinkry\Components\CssLoader;
use Tulinkry\Components\JsLoader;
use Tulinkry;
use Tulinkry\Services\ParameterService;
use Nette\Application\Responses;

use Nette\Http\IResponse;
/**
 * Base presenter for all application presenters.
 */
abstract class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $lang;
	
	/** @persistent */
	public $backlink = '';

	/**
	 * @var ParameterService
	 * @inject
	 */
	protected $parameters;


	public function startup ()
	{
		parent::startup ();

	    $this -> template -> isAjax = $this -> isAjax();
	    $this -> template -> invalidSnippets = array();
	    $this -> template -> numberOfSnippets = 0;
	    if ( $this -> isAjax () )
	    {
	    	$this -> invalidateControl ( "paginator" );
	    }
	}

	public function beforeRender ()
	{
		parent::beforeRender ();
		$this -> template -> paginator = $this [ "paginator" ] -> getPaginator ();
	}


	public function getPaginator ()
	{
		return $this [ "paginator" ] -> getPaginator ();
	}	

	public function getNames ()
	{
		$name = explode ( ":", $this -> name );
		return $name [ count ( $name ) - 1 ];
	}

	public function getModule ()
	{
		$name = explode ( ":", $this -> name );
		unset ( $name [ count ( $name ) - 1 ] );
		return implode ( ":", $name );
	}

	public function handleLogout ()
	{
		$this -> user -> logout ( true );
		$this -> flashMessage ( "Byl jste úspěšně odhlášen.", "success" );
		$this -> redirect ( "this" );
	}

	/**
	 * @param ParameterService
	 */
	public function injectParameters(ParameterService $parameterService)  // trait
	{ 
		$this->parameters = $parameterService;
	} 


	/**
	 * @return CssLoader
	 */
	protected function createComponentCss()
	{
		return $this->context->getService('cssControl')->create();
	}

	/**
	 * @return JsLoader
	 */
	protected function createComponentJs()
	{
		return $this->context->getService('jsControl')->create();
	}


	protected function createComponentMainMenu()
	{
		if ( ! array_key_exists ( "menu", $this -> parameters -> params ) )
			throw new Exception("Missing section 'menu' in configuration.");
		$l = $this -> parameters -> params [ "menu" ];
		return $this->context->menuControl->create( $l );
	}

	protected function createComponentPaginator ( $name )
	{
	    $visualPaginator = new Tulinkry\Components\VisualPaginator();
	    $visualPaginator -> paginator -> itemsPerPage = 10;
	    if ( array_key_exists ( "paginator", $this -> parameters -> params ) &&
	    	 array_key_exists ( "itemsPerPage", $this -> parameters -> params [ "paginator" ] ) )
	    	$visualPaginator -> paginator -> itemsPerPage = intval ( $this -> parameters -> params [ "paginator" ] [ "itemsPerPage" ] );
	    return $this [ $name ] = $visualPaginator;
	}

	/**
	 * Handles requests to create component / form?
	 * @param string
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);
		if ($component === NULL) {
			$componentClass = "Tulinkry\\Components\\" . $name . "Control";
			if (class_exists($componentClass)) {
				$component = new $componentClass;
			}
		}

		return $component;
	}


	public function flashMessage($message, $type = "success")
	{		
		if ($this->getContext()->hasService("translator")) {
			$message = $this->getContext()->translator->translate($message);
		}

		$this -> invalidateControl ( "flashMessage" );
		$this -> invalidateControl ( "flashMessageArea" );

		return parent::flashMessage($message, $type);
	}
/*
	public function signalReceived( $signal )
	{
		try {
			parent::signalReceived ( $signal );
		} catch ( \Exception $e )
		{
			$this -> payload -> exception = $e;
			$this -> payload -> error = $e -> getMessage ();
		}
	}
*/
	protected function existsJsExtension ( $name )
	{
		return array_key_exists( "js-extensions", $this -> parameters -> params ) && array_key_exists( $name, $this -> parameters -> params [ "js-extensions" ] );
	}

	public function processSignal ()
	{
		if ( 1 && $this -> existsJsExtension ( "ajax-progress-bar" ) && $this -> isAjax () )
		{
			// sends error indicated by flash message if call is successfull
			// and js can then react by the type of the message
			// 
			// instead of only sending error 500 it sends also a payload of flash messages
			// which were stored by the request
			try {
				parent::processSignal ();

				$id = $this->getParameterId('flash');
				$this -> payload -> flashes = $this->getPresenter()->getFlashSession()->$id;	
			} 
			catch ( \Nette\Application\AbortException $e ) 
			{ 			
				// workaround for terminating exception sent by nette
				throw $e;			
			}
			catch ( \Exception $e )
			{
				//$this -> payload -> error = $e -> getMessage ();
				$this -> payload -> status = IResponse::S500_INTERNAL_SERVER_ERROR;
				$this -> payload -> statusMsg = "Internal server error";

				$id = $this->getParameterId('flash');
				$this -> payload -> flashes = $this->getPresenter()->getFlashSession()->$id;

				// change code to 500 internal error
				$this -> getHttpResponse() -> setCode ( IResponse::S500_INTERNAL_SERVER_ERROR );

				// send payload
				$this -> sendPayload ();
				

				throw $e;
			}
		}
		else
		{
			parent::processSignal ();
		}
	}

	public function redirectLogin ( $params = [] )
	{
		$params = array_merge( $params, array ( 'backlink' => $this -> storeRequest () ) );
		$this -> redirect ( "Sign:login", $params );
	}

	protected function createTemplate($class = NULL)
	{
	    $template = parent::createTemplate($class);

	    $template->addFilter('metres', function ($s) {
	        
	        if ( $s < 1000 )
	        	return round ( $s, 1 ) . " m";
	        else
	        	return round ( $s / 1000, 1 ) . " km";
	    });

	    $template->addFilter('degree', function ($s) use ($template) {
	        return $s . html_entity_decode('&deg;', ENT_NOQUOTES,'UTF-8');
	    });

	    $template->addFilter('timeleft', function ($s) use ($template) {
	    	if( ! $s instanceof \DateTime )
	    		return $s;
	    	$diff = $s->diff(new \Tulinkry\DateTime());
	    	$r = "";
	    	foreach ( [ 'r' => 'y', 'měs.' => 'm', 'd' => 'd', 
	    				'h' => 'h', 'min.' => 'i', 'sec.' => 's' ] as $val => $type )
	    		if ($diff->$type) {
	    			$r = $diff->$type . ' ' . $val;
	    			break;
	    		}
	        return $r;
	    });

	    return $template;
	}

}
