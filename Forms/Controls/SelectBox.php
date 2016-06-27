<?php

namespace Tulinkry\Forms\Controls;

use Nette;
use Tulinkry\Model\Doctrine\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

class SelectBox extends Nette\Forms\Controls\SelectBox
{

	public function setItems ( array $items, $useKeys = TRUE )
	{

		$items = $this -> toArray ( $items );
		parent::setItems ( $items, $useKeys );
		return $this;
	}

	protected function array_map ( callable $val, callable $key, $array )
	{
     	$newArray = array ();
    	foreach ( $array as $kl => $v )
    	{
    		if ( is_array ( $v ) ||
    			 $v instanceof ArrayCollection ||
    			 $v instanceof PersistentCollection )
    		{
    			$newArray [ $kl ] = $this -> array_map ( $val, $key, $v );
    			continue;
    		}
    		$k = $key ( $v );
    		//echo $kl."|".$k."<br>";
    		if ( $k < 0 )
    			$k = $kl;

    		$newArray [ $k ] = $val ( $v );
    	}
    	//print_r ( $newArray );
    	//echo "<br>";
    	return $newArray;   		
	}

	protected function toItems ( $collection )
	{
		return $this -> array_map ( function ( $entity ) {
			if ( is_object ( $entity ) && method_exists( $entity, "getDescription" ) )
				return $entity -> getDescription ();
			return $entity;
		},
		function ( $entity ) {
			if ( is_object ( $entity ) && method_exists( $entity, "getId" ) )
				return $entity -> getId ();
			return -1;
		},
		$collection );    	
	}

	protected function toArray ( $items )
	{	
	    return $this -> toItems ( $items );		
	}
};	