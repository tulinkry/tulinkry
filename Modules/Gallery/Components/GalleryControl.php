<?php

namespace Tulinkry\Gallery;

use Nette;
use Tulinkry;


class GalleryControl extends Tulinkry\Application\UI\Control
{
	protected $model;

	protected $columns = 3;
	/** @persistent */
	public $offset = 0;
	public $limit = 12;

	protected $count = 0;

	public function __construct ( $model, $columns = null )
	{
		$this -> model = $model;
		if ( $columns ) $this -> columns = $columns;
	}


	public function handleNext ()
	{
		$this -> offset += $this -> limit;
		$this -> invalidateControl ( "images" );
		$this -> invalidateControl ( "next" );
	}


	protected function getImages ()
	{
		$images = $this -> model -> getImages ( $this -> limit, $this -> offset );
		$photos = [];
		foreach ( $images as $key => $image )
		{
			if ( $image instanceof Gallery )
			{
				// we have a gallery mode so this is the gallery
				$photos [] = $image;
				$photos = array_merge ( $photos, $image -> getImages () );

			}
			elseif ( $image instanceof Photo )
			{
				// it is a photo
				$photos [] = $image;
			}
			else
				throw new Exception("Uknown");
				
		}
		$this -> count = count ( $photos );
		return $photos;
	}

	protected function columns ()
	{
		//by ( [ "parent" => NULL ], [ "rank" => "DESC", "datum" => "DESC" ] );
		$photos = $this -> getImages ();

		$columns = [];
		for ( $i = 0; $i < $this -> columns; $i ++ )
			$columns [ $i ] = [];
		$cnt = count ( $photos );
		$j = 0;
		for ( $j = 0; $j < $cnt; )
		{
			for ( $i = 0; $i < $this -> columns && $j < $cnt; $i ++, $j ++ )
			{
				// create number of columns
				$columns [ $i ] [] = $photos [ $j ];
			}
		}
		return $columns;
	}

	protected function divide ()
	{
		$ret = [];
		if ( $this -> columns > 12 )
			throw new Exception("Error Processing Request", 1);
			
		if ( 12 % $this -> columns == 0 )
		{
			$div = floor ( 12 / $this -> columns );
			for ( $i = 0; $i < $this -> columns; $i ++ )
				$ret [$i] = $div;
		}
		elseif ( $this -> columns > 6 && $this -> columns < 12)
		{
			$ret [] = $this -> columns;
			$ret [] = 12 - $this -> columns;
		}
		else
		{
			// 5
			$ret [] = $this -> columns;
			$ret [] = $this -> columns;
			$ret [] = 12 - $this -> columns - $this -> columns;
		}
		return $ret;
	}

	public function render ()
	{
		$this -> template -> setFile ( __DIR__ . "/galleryControl.latte" );

		$this -> template -> columns = $this -> columns ();
		$this -> template -> colNumbers = $this -> divide ();
		$this -> template -> limit = $this -> limit;
		$this -> template -> count = $this -> count;
		//$this -> template -> photos = $this -> model -> by ( [ "parent" => NULL ], [ "rank" => "DESC", "datum" => "DESC" ] );

		$this -> template -> render ();
	}

}