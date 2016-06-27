<?php


namespace Tulinkry\Gallery;

use Nette;

class Image extends Nette\Image
{
	const AS_IMAGE = -1;


	protected $width;
	protected $height;

	protected $path;

	public function __construct ( $path )
	{
		$this -> path = $path;
		parent::__construct ( $path );
	}

	public function getPath ()
	{
		return $this -> path;
	}

/*

	public function thumbnail ( $width, $height )
	{
		if ( $this -> width > $this -> height )
		{
			$thumbnail_width = $width;
			$thumbnail_height = (int) ( $thumbnail_width * $this -> height / $this -> width );
		}
		else
		{
			$thumbnail_height = $height;
			$thumbnail_width = (int) ( $thumbnail_height * $this -> width / $this -> height );
		}


	}


	*/
}