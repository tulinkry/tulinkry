<?php

namespace Tulinkry\Components;


use Tulinkry\Application\UI\Control;
use Tulinkry;
use Exception;
use Nette\Utils\Html;

interface IMenuControl
{
	/** @return MenuControl */
	public function create ( $array );
}


class MenuControl extends Control
{
	protected $wrapper = "";
	protected $outerElement = "";
	protected $innerElement = "";

	protected $links = array ();

	public function __construct ( array $config, $innerElement = "", $outerElement = "", $wrapper = "" )
	{

		if ( isset ( $config [ "links" ] ) )
			$this -> links = $config [ "links" ];

		if ( isset ( $config [ "outer" ] ) )
			$this -> outerElement = $config [ "outer" ];
		else
			$this -> outerElement = $outerElement;

		if ( isset ( $config [ "inner" ] ) )
			$this -> innerElement = $config [ "inner" ];
		else
			$this ->  innerElement = $innerElement;

		if ( isset ( $config [ "wrapper" ] ) )
			$this -> wrapper = $config [ "wrapper" ];
		else
			$this -> wrapper = $wrapper;

	}


	protected function linkify ( array $links )
	{
		//Html::el("link")->rel("stylesheet")->type("text/css")->media($this->media)->href($source);
		$res = [];
		foreach ( $links as $key => $link )
		{
			if ( array_key_exists ( "element", $link ) )
				$e = Html::el ( $link [ "element" ] );
			else
				$e = Html::el ( "a" );

			if ( array_key_exists ( "text", $link ) )
				$e -> add ( $link [ "text" ] );
			if ( array_key_exists ( "html", $link ) )
				$e -> add ( $link [ "html" ] );
			if ( array_key_exists ( "attr", $link ) )
				$e -> addAttributes ( $link [ "attr" ] );

			$e -> setHref ( $this -> presenter -> link ( $key ) );

			//$res [ $key ] = Html::el ( "a" ) -> href ( $this -> presenter -> link ( $key ) ) -> setHtml ( $link );
			//$res [ $key ] -> class = "btn btn-lg btn-default text-center hidden";
			$res [ $key ] = $e;
		}

		return $res;
	}

	protected function makeEl ( $section, $element = "a" )
	{
		if ( is_string ( $section ) )
			$e = Html::el ( $section );
		elseif ( array_key_exists ( "element", $section ) )
			$e = Html::el ( $section [ "element" ] );
		else
			$e = Html::el ( $element );
		if ( array_key_exists ( "text", $section ) )
			$e -> add ( $section [ "text" ] );
		if ( array_key_exists ( "html", $section ) )
			$e -> add ( $section [ "html" ] );
		if ( array_key_exists ( "attr", $section ) )
			$e -> addAttributes ( $section [ "attr" ] );
		return $e;
	}


	public function render ()
	{


		$links = $this -> linkify ( $this -> links );

		$outer = $this -> makeEl ( $this -> outerElement, "ul" );
		

		foreach ( $links as $key => $link )
		{
			if ( $this -> presenter -> isLinkCurrent ( $key ) )
				$link -> class = $link -> class . " active";

			if ( array_key_exists ( "wrapper", $this -> links [ $key ] ) )
			{
				$innerWrapper = $this -> makeEl ( $this -> links [ $key ] [ "wrapper" ], "li" );
				if ( $this -> presenter -> isLinkCurrent ( $key ) )
					$innerWrapper -> class = $innerWrapper -> class . " active";
				$innerWrapper -> add ( $link );
			}
			else
				$innerWrapper = $link;
			if ( $this -> innerElement )
			{
				$inner = $this -> makeEl ( $this -> innerElement, "li" );
				$inner -> add ( $innerWrapper );
			}
			else
				$inner = $innerWrapper;

			$outer -> add ( $inner );
		}

		if ( $this -> wrapper )
		{
			$wrapper = $this -> makeEl ( $this -> wrapper, "div" );
			$wrapper -> add ( $outer );
		}
		else
			$wrapper = $outer;

		echo $wrapper;
	}
};