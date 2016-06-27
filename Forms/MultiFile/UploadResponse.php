<?php

namespace Tulinkry;

use Nette;

class UploadResponse extends Nette\Object
{

	protected $path;
	protected $thumbnail_path;
	protected $image;


	public function __construct ( $path, $thumbnail_path, $image )
	{
		$this -> path = $path;
		$this -> thumbnail_path = $thumbnail_path;
		$this -> image = $image;
	}

	public function getPath ()
	{
		return $this -> path;
	}

	public function getImage ()
	{
		return $this -> image;
	}

	public function getThumbnail ()
	{
		return $this -> thumbnail_path;
	}



}