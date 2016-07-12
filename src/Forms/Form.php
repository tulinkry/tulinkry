<?php

namespace Tulinkry\Forms;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls as NControls;

class Form extends Nette\Application\UI\Form
{

	//protected $translator;
	public $useBootstrap = true;


	protected function attached($presenter)
	{
		parent::attached($presenter);

		if ($presenter -> context -> hasService ( "translator" ) )
		{ 
			$this -> translator = $presenter -> context -> getService ( "translator" );
			$this -> setTranslator ( $this->translator );
		}

		if ($presenter instanceof Nette\Application\IPresenter) 
		{
			$this->attachHandlers($presenter);
		}

		$this -> attachClasses ();

	}


	protected function attachClasses ()
	{

		if ( ! $this -> useBootstrap )
			return;

		foreach ($this->getControls() as $control) 
		{
			if ($control instanceof NControls\Button) 
			{
				if($control->getControlPrototype()->hasClass('btn')){
					continue;
				}
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif ($control instanceof NControls\TextBase) 
			{
				$control->getControlPrototype()->addClass('form-control');
			} elseif ($control instanceof NControls\SelectBox || 
					  $control instanceof NControls\MultiSelectBox) 
			{
				$control->getControlPrototype()->addClass('form-control');
				$control -> getControlPrototype() -> addClass ( "selectpicker form-control" );
				$control -> setAttribute ( "data-live-search", "true" );

			} elseif ($control instanceof NControls\Checkbox || 
					  $control instanceof NControls\CheckboxList || 
					  $control instanceof NControls\RadioList) 
			{
				//if ($control->getSeparatorPrototype()->getName()!== NULL)
				//	$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}


	}

	public function __construct ( $parent = NULL, $name = NULL )
	{
		parent::__construct ( $parent, $name );
		//$this -> monitor ( "Nette\Application\IPresenter" );

		if ( $this -> useBootstrap )
		{
			$this -> setRenderer ( new \Tulinkry\Forms\Rendering\CustomRenderer );
		}
	}
/*
	public function addSelect( $name, $label = NULL, array $items = NULL, $size = NULL )
	{
		return $this[$name] = new SelectBox($label, $items, $size);
	}
*/



	public function render ()
	{
		if ( ! $this -> useBootstrap )
		{
	        echo call_user_func_array(array($this->getRenderer(), 'render'), $args);
			return;		
		}


		$this -> attachClasses ( $this -> presenter );

        $args = func_get_args();
		if ( count ( $args ) > 0 )
		{
			if ( $args [ 0 ] == "horizontal" )
			{
				$this -> getElementPrototype() -> class('form-horizontal');
				$renderer = $this -> getRenderer ();
				$renderer -> wrappers [ 'controls' ] [ 'container' ] = "ul class=\"form-inline\"";
				array_shift ( $args );
			}
			elseif ( $args [ 0 ] == "vertical" )
			{
				array_shift ( $args );
			}
		}

        array_unshift($args, $this);
        echo call_user_func_array(array($this->getRenderer(), 'render'), $args);
	}



	public function addContainer ( $name )
	{
	    $control = new Container;
	    $control -> currentGroup = $this -> currentGroup;
	    return $this[ $name ] = $control;
	}


	public function addSelect( $name, $label = NULL, array $items = NULL, $size = NULL )
	{
		return $this[$name] = new Controls\SelectBox($label, $items, $size);
	}

	public function addDate ( $name, $label = NULL, $cols = NULL, $maxLength = NULL )
	{
		return $this[$name] = new Controls\DateInput ( $label, $cols, $maxLength );
	}

	public function addEmail($name, $label = NULL, $cols = NULL, $maxLength = NULL)
	{
		$item = $this->addText($name, $label, $cols, $maxLength);
		$item->addCondition(self::FILLED)
			->addRule(self::EMAIL, "Email nemá správný formát.");

		return $item;
	}	

	public function addTextArea ( $name, $label = NULL, $cols = 40, $rows = 10 )
	{
		return $this[$name] = new Controls\TextArea ( $label, $cols, $rows );
	}
/*
	public function addCheckbox($name, $caption = NULL)
	{
		return $this[$name] = new Controls\Checkbox($caption);
	}
*/

	protected function attachHandlers($presenter)
	{
		// $formNameSent = lcfirst($this->getName())."Sent";
		$formNameSent = "process" . lcfirst ( $this -> getName() );

		$possibleMethods = array(
			array( $presenter, $formNameSent ),
			array( $this -> parent, $formNameSent ),
			array( $this, "process" ),
			array( $this -> parent, "process")
		);

		foreach ( $possibleMethods as $method ) 
		{
			if ( method_exists( $method[0], $method[1] ) )
			{
				$this -> onSuccess[] = array( $method[0], $method[1] );
			}
		}
	}

}