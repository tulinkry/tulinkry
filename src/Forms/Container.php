<?php

namespace Tulinkry\Forms;

use Nette;
use Tulinkry\Forms\Controls\SelectBox;
use Tulinkry\Forms\Controls\DateInput;
use Tulinkry\Forms\Controls\EmailInput;


class Container extends Nette\Forms\Container
{

	public function addSelect( $name, $label = NULL, array $items = NULL, $size = NULL )
	{
		return $this[$name] = new SelectBox($label, $items, $size);
	}

	public function addDate ( $name, $label = NULL, $cols = NULL, $maxLength = NULL )
	{
		return $this[$name] = new DateInput ( $label, $cols, $maxLength );
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
	}*/

}