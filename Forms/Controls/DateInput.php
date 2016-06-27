<?php

namespace Tulinkry\Forms\Controls;

use Nette;
use Tulinkry;
use Nette\Utils\Html;

class DateInput extends Nette\Forms\Controls\TextInput
{

	/**
	 * @var string
	 */
	protected $mask = "Y-m-d H:i:s";
	protected $database_mask = "Y-m-d H:i:s";
	protected $className = "datetimepicker";

	public $onChange = array ();

	protected function getDataDateFormat ()
	{
		$mask = preg_replace ( "/d/", "DD", $this -> mask );
		$mask = preg_replace ( "/m/", "MM", $mask );
		$mask = preg_replace ( "/Y/", "YYYY", $mask );
		$mask = preg_replace ( "/H/", "HH", $mask );
		$mask = preg_replace ( "/i/", "mm", $mask );
		$mask = preg_replace ( "/s/", "ss", $mask );
		return $mask;
	}

	public function setMask ( $mask )
	{
		$this -> mask = $mask;
		return $this;
	}

	public function getMask ()
	{
		return $this -> mask;
	}

	public function setValue ( $value )
	{
		$this -> value = '';
		if ( is_object ( $value ) )
		{
			// test for datetime
			if ( ( $value instanceof Tulinkry\DateTime || 0 ) &&
				 ( method_exists ( $value, "format" ) ) )
				$this -> value = $value -> format ( $this -> database_mask );
		}
		else if ( is_array ( $value ) )
		{
		}
		else
		{
			if ( is_string ( $value ) && ! empty ( $value ) )
			{
				$value = str_replace("\r\n", "\n", $value );
				if ( strlen ( $value ) < 4 )
					$value = "00:" . $value;
				if ( strlen ( $value ) < 6 )
					$value = "00:" . $value;
				if ( strlen ( $value ) < 9 )
					$value = new Tulinkry\DateTime ( $value );
				else
					$value = Tulinkry\DateTime::createFromFormat ( $this -> mask, $value );
				$this -> value = $value -> format ( $this -> database_mask );
			}
		}
		return parent::setValue ( $this -> value );
	}	

	public function getValue ()
	{
		return Tulinkry\DateTime::createFromFormat ( $this -> database_mask, $this -> value );
	}

	public function getRawValue()
	{
		return $this->rawValue;
	}

	public function getControl()
	{
		$this -> setAttribute ( "data-date-format=\"" . $this -> getDataDateFormat () . "\"" );
		$this -> getControlPrototype() -> class = $this -> getControlPrototype() -> class + [ $this -> className ];

		$input = parent::getControl();

		$input -> value = Tulinkry\DateTime::createFromFormat ( $this -> database_mask, $input -> value );
		$input -> value = $input -> value -> format ( $this -> mask );
		

		// dependency on other date
		$ret = Html::el ( "div" );
		$ret -> add ( $input );

		$str = "";
		foreach ( $this -> onChange as $change )
			$str .= $change;

		$ret -> add ( Html::el ( "script" ) -> setHtml ( $str ) );

		return $ret;
	}


	public function linked ( DateInput $other )
	{
		$dependency_on = [ $other ];
		$template = "$(window).on('load',function(){\n"
		   . "\t$(\"#%s\").on(\"dp.change\",function (e) {\n"
		   . "\t\t//alert('aaa');\n"
		   . "\t\t$('#%s').data(\"DateTimePicker\").minDate(e.date);\n"
		   . "\t\tif ( $('#%s').data(\"DateTimePicker\").date() < $('#%s').data(\"DateTimePicker\").date() )\n"
		   . "\t\t\t$('#%s').data(\"DateTimePicker\").date(e.date);\n"
           . "\t});\n"
		   . "});";
		$txt = "";
		foreach ( $dependency_on as $dep )
		{
			$id = $dep -> getHtmlId ();
			$id2 = $this -> getHtmlId ();
			$txt .= sprintf ( $template, $id, $id2, $id2, $id, $id2 ) . "\n\n";
		}
		
		//$this -> setOption ( "description", Html::el('script') -> setHtml ( $txt ) );
		$this -> onChange [] = $txt; //Html::el('script') -> setHtml ( $txt );
		return $this;
	}
};