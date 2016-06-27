<?php

namespace Tulinkry\Gallery;

use Nette;

class Gallery extends Nette\Object
{

	protected $info;
	protected $name;
	protected $date;
	protected $images;

	public function __construct ( $name, $info, $date, $images )
	{
		$this -> name = $name;
		$this -> date = $date;
		$this -> info = $info;
		$this -> images = $images;
	}


	public function getImages ()
	{
		return $this -> images;
	}


	public function getInfo ()
	{
		return $this -> info;
	}

	public function getDate ()
	{
		return $this -> date;
	}

	public function getName ()
	{
		return $this -> name;
	}

}