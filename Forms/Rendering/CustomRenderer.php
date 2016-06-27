<?php

namespace Tulinkry\Forms\Rendering;

use Nette,
	Nette\Utils\Html;
use Nette\Forms\Rendering as NRendering;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls as NControls;


class CustomRenderer extends NRendering\DefaultFormRenderer
{


	public function __construct ()
	{
		$this -> wrappers['controls']['container'] = NULL;
		$this -> wrappers['pair']['container'] = 'div class="form-group clearfix"';
		$this -> wrappers['pair']['.error'] = 'has-error';
		$this -> wrappers['control']['container'] = 'div class=col-sm-9';
		$this -> wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$this -> wrappers['control']['description'] = "span class='help-block'";
		$this -> wrappers['control']['description'] = "div class=col-sm-3";
		$this -> wrappers['control']['errorcontainer'] = 'span class=help-block';
	}



	public function renderDescription ( Nette\Forms\IControl $control )
	{
	    $description = $control->getOption('description');
	    if ($description instanceof Html) {
	        $description = ' ' . $description;
	    } elseif (is_string($description)) {
	        $description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));
	    } else {
	        $description = '';
	    }
	    if ($control->isRequired()) {
	        $description = $this->getValue('control requiredsuffix') . $description;
	    }
	    return $description;
	}

	/**
	 * Renders 'control' part of visual row of controls.
	 * @return string
	 */
	public function renderControl(Nette\Forms\IControl $control)
	{

	    $body = $this->getWrapper('control container');

	    if ($this->counter % 2) {
	        $body->class($this->getValue('control .odd'), TRUE);
	    }

	    $control->setOption('rendered', TRUE);
	    $el = $control->getControl();

	    if ( $control instanceof Checkbox ) {
	    	// move label to the other labels
	    	/*$input = $el->offsetGet(0);
	    	$input = preg_replace ( "/(<label.*?><input.*?>)(.*?)(<\/label>)/", "$1$3", $input );
	    	$el->setHtml($input);*/
	    }

	    if ($el instanceof Html && $el->getName() === 'input') {
	        $el->class($this->getValue("control .$el->type"), TRUE);
	    }
	    return $body->setHtml($el . $this->renderErrors($control));
	}


	/**
	 * Renders 'label' part of visual row of controls.
	 * @return string
	 */
	public function renderLabel(Nette\Forms\IControl $control)
	{
		$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
		$label = $control->getLabel();
		if ($control instanceof Checkbox) {
			$label = '<label for=' . $control->getHtmlId() . '>' . $control->translate($control->caption) . '</label>';
		}
		if ($label instanceof Html) {
			$label->add($suffix);
		} elseif ($label != NULL) { // @intentionally ==
			$label .= $suffix;
		}
		return $this->getWrapper('label container')->setHtml($label);
	}


	public function renderPair(Nette\Forms\IControl $control)
	{
	    $pair = $this->getWrapper('pair container');

	    if ( $control -> getOption ( "description", NULL ) !== NULL )
	    	$this -> wrappers['control']['container'] = 'div class=col-sm-6';
	    
		$pair->add($this->renderLabel($control));
		$pair->add($this->renderControl($control));


	    if ( $control -> getOption ( "description", NULL ) !== NULL )
	    {
		    $pair->add($this->renderDescription($control));
	    	$this -> wrappers['control']['container'] = 'div class=col-sm-9';
	    	//$pair->add(Html::el("div class=clearfix"));
	    }

	    $pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
	    $pair->class($control->hasErrors() ? $this->getValue('pair .error') : NULL, TRUE);
	    $pair->class($control->getOption('class'), TRUE);
	    if (++$this->counter % 2) {
	        $pair->class($this->getValue('pair .odd'), TRUE);
	    }
	    $pair->id = $control->getOption('id');
	    return $pair->render(0);
	}

};