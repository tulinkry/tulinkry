<?php

namespace Tulinkry\Gallery;

use Nette;

class Photo extends Nette\Object
{

	protected $id;
	protected $info;
	protected $path;
	protected $thumbnail;
	protected $date;

	public function __construct ( $id, $info, $path, $thumbnail, $date )
	{
		$this -> id = $id;
		$this -> date = $date;
		$this -> info = $info;
		$this -> thumbnail = $thumbnail;
		$this -> path = $path;
	}


	public function getThumbnail ()
	{
		return $this -> thumbnail;
	}


	public function getInfo ()
	{
		return $this -> info;
	}

	public function getDate ()
	{
		return $this -> date;
	}

	public function getId ()
	{
		return $this -> id;
	}

	public function getPath ()
	{
		return $this -> path;
	}
}
