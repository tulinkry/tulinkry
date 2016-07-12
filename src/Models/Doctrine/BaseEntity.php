<?php


namespace Tulinkry\Model\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nette\Object;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;



class BaseEntity extends Object implements IBaseEntity
{
	const useId = false;


    public function toArray()
	{
        $reflection = new \ReflectionClass($this);
        
        if ( preg_match ( "/Entity\\\.*$/", $reflection -> getName () ) )
        {
        } 
        else if ( preg_match ( "/[Pp]roxy/", $reflection -> getName () ) )
        {
        	$reflection = $reflection -> getParentClass ();
        }

        $details = array();

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE) as $property)
        {
        	if ( ! $property -> isStatic () )
        	{
        		$name = $property->getName();
        		$words = explode ( "_", $name );
        		$get = $words [ 0 ];
        		for ( $i = 1; $i < count ( $words ); $i ++ )
        			$get .= ucfirst ( $words [ $i ] );
        		$getter = "get" . ucfirst( $get );
                $isser = "is" . ucfirst( $get );

                if ( method_exists ( $this, $getter ) )
            		$value = $this -> $getter ();
                else if ( method_exists( $this, $isser ) )
                    $value = $this -> $isser ();

        		//if ( is_object( $value ) )
        		//	$details [] = get_class ( $value );
        		if ( $value instanceof BaseEntity )
        		{
        			$value = $value -> getId ();
        			$details [ $name ] = $value;
        		}
        		else if ( $value instanceof ArrayCollection || $value instanceof PersistentCollection )
        		{
                    /*
        			$value = $this -> array_map ( function ( BaseEntity $entity ) {
        				return $entity -> getId ();
        			},
        			function ( BaseEntity $entity ) {
        				return $entity -> getId ();
        			},
        			$value -> toArray () );
        			$details [ $name ] = $value;
                    */
        		}
        		else if ( is_object ( $value ) )
        		{
    				$ref = new \ReflectionClass ( $value );
			        if ( preg_match ( "/[Pp]roxy/", $ref -> getName () ) )
			        {
			        	$ref = $ref -> getParentClass ();
			        }
			        if ( preg_match ( "/DateTime/", $ref -> getName () ) )
			        {
			        	//$value = $value -> format ( "Y-m-d H:i:s" );
			        }
    				$details [ $name ] = $value;
        		}
      			else if ( is_bool ( $value ) )
                {
      				$value = (int)$value;
                    $details [ $name ] = $value;

                }
       			else
                {

       			  $details [ $name ] = $value;
                }
        		//$details [] = $property -> getName ();
        		//$details [] = (int)($value instanceof IEntity);
        		//$details [] = $property;
        	}
        }
        return $details;
    }

    public function array_map ( callable $val, callable $key, array $array )
    {
    	$newArray = array ();
    	foreach ( $array as $v )
    		$newArray [ $key ( $v ) ] = $val ( $v );
    	return $newArray;
    }

    public function toSelect ( $collection )
    {
		$collection = $this -> array_map ( function ( BaseEntity $entity ) {
			return $entity -> getDescription ();
		},
		function ( BaseEntity $entity ) {
			return $entity -> getId ();
		},
		$collection -> toArray () );    	
    	return $collection;
    }

    public function getDescription ()
    {
    	$d = $this -> getId ();
    	if ( self::useId )
    		$d .= " [" . $this -> getId () . "]";
    	return $d;
    }
};



/*

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property)
        {
            if (!$property->isStatic())
            {
                $value = $this->{$property->getName()};

                if ($value instanceof IEntity) 
                {
                    $value = $value->getId();
                } elseif ($value instanceof ArrayCollection || $value instanceof PersistentCollection) 
                {
                    $value = array_map(function (BaseEntity $entity) 
					                    {
					                        return $entity->getId();
					                    }, 
					                    $value->toArray()
                    				);
                }
                $details[ $property->getName() ] = $value;
            }
        }

*/