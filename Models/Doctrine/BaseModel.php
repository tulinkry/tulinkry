<?php
/**
 * @author Kryštof Tulinger
 *
 */

namespace Tulinkry\Model\Doctrine;

use Kdyby\Doctrine\EntityManager;
use Tulinkry;
use Doctrine\ORM\UnitOfWork;
use Kdyby\Doctrine\MissingClassException;
use Traversable;

use Doctrine\ORM\NoResultException;
use Tulinkry\Utils\Strings;
use Nette;

class BaseModel extends Tulinkry\Model\BaseModel implements IBaseModel
{

    /** @var Kdyby\Doctrine\EntityManager */
    protected $em;
    /** @var Kdyby\Doctrine\EntityDao */
    protected $dao;

    protected $ONETOMANY = 4; 
    protected $MANYTOONE = 2; 
    protected $ONETOONE = -1; 
    protected $MANYTOMANY = -1;


    public $entityPrefixes = [ "Entity", 
                               "App\\Entity", 
                               "Tulinkry\\Model\\Doctrine\\Entity",
                               "Tulinkry\\Entity", 
                               "Tulinkry\\Model\\Entity"
                             ];

	public function __construct ( EntityManager $em )
	{
		$this -> em = $em;
        $ref = $this -> getReflection ();
        $name = preg_replace ( "/Model/", "", $ref -> getShortName () );
        $exp = NULL;
        try {
            $this -> dao = $em->getDao($name);
            $exp = NULL;
        } catch ( MissingClassException $e )
        {
            $exp = $e;
            foreach ( $this -> entityPrefixes as $prefix )
                try {
                    $this -> dao = $em -> getDao ( $prefix . "\\" . $name );
                    $exp = NULL;
                    break;
                } catch ( MissingClassException $e )
                {
                    $exp = $e;
                }
        }
        if ( ! $this -> dao || $exp )
            throw $exp;
	}

    public function flush ()
    {
        $this -> em -> flush ();
    }


    public function update ( $entity )
    {
        if ( $this -> em -> getUnitOfWork () -> getEntityState ( $entity ) === UnitOfWork::STATE_MANAGED );
            $this -> em -> flush ( $entity );
    }

	public function refresh ( $entity )
	{
		$this -> em -> refresh ( $entity );
	}

	public function detach ( $entity )
	{
		if ( $this -> em -> getUnitOfWork () -> getEntityState ( $entity ) === UnitOfWork::STATE_DETACHED )
			return;
		$this -> em -> detach ( $entity );
	}

	public function merge ( $entity )
	{
		//if ( $this -> em -> getUnitOfWork () -> getEntityState ( $entity ) != \Doctrine\ORM\UnitOfWork::STATE_DETACHED )
		//	throw new \Nette\InvalidStateException ( "Entity is not in detached state." );
		return $this -> em -> merge ( $entity );
	}

	/**
     * @param object
     * @param bool
     */
    public function insert($entity, $transactional = self::FLUSH)
    {
        $this->em->persist($entity);
        if ($transactional == self::FLUSH) 
        {
                $this->em->flush($entity);
        }

        return $this;
    }


	/**
     * @param object
     * @param bool
     */
    public function remove($entity, $transactional = self::FLUSH)
    {
        $this->em->remove($entity);
        if ($transactional == self::FLUSH) 
        {
        	$this->em->flush($entity);
    	}
        return $this;
    }

    /**
     * @param mixed
     * @return object
     */
    public function item($id)
    {
        return $this->dao->find($id);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->dao->findAll();
    }

    /**
     * @param array
     * @return object|NULL
     */
    public function one($criteria = array ())
    {
        return $this->dao->findOneBy($criteria);
    }

    /**
     * @param array
     * @param array|NULL
     * @param int|NULL
     * @param int|NULL
     * @return array
     */
    public function by($by = array (), $order = array (), $limit = NULL, $offset = NULL)
    {
        return $this->dao->findBy($by, $order, $limit, $offset);
    }

    /**
     * @param int|NULL
     * @param int|NULL
     * @param array|NULL
     */
    public function limit($limit = NULL, $offset = NULL, $by = array (), $orderBy = NULL)
    {
        return $this->dao->findBy( $by, $orderBy, $limit, $offset);
    }

    /**
     *
     * @return int
     *
     */
    public function count ( $criteria = array () )
    {
            $query = $this -> dao -> countBy ( $criteria );
            return $query;
    }

    /**
     * Creates a new entity class and tries to hydrate the object
     * @var array with entity properties
     */
    public function create ( $entity_array = array () )
    {
        // $entity = new $this -> name
        // $entity -> fromArray ( $entity_array );
        $meta = $this -> dao -> getClassMetadata ();


        $entity = new $meta -> name;

        //$meta = $meta -> getReflectionProperties ();
        //echo "<pre>";
        //print_r ( $meta );
        //echo "</pre>";

        foreach ( $meta -> fieldMappings as $key => $field )
        {
            //$key = ucfirst(Strings::camelize ( $key ));
            if ( array_key_exists ( $key, $entity_array ) )
                $entity -> $key = $entity_array [ $key ];
        }

        foreach ( $meta -> associationMappings as $key => $mapping )
        {
            //$key = Strings::camelize ( $key );

            if ( $mapping [ "type" ] == $this -> ONETOMANY )
            {
                if ( array_key_exists ( $key, $entity_array ) )
                {
                    if ( is_array ( $entity_array [ $key ] ) /* or collection ? */ )
                    {
                        foreach ( $entity_array [ $key ] as $id => $object )
                        {
                            $adder = "add" . ucfirst ( $key );
                            if ( ! method_exists ( $entity, $adder ) )
                            {
                                if ( $adder [ strlen ( $adder ) - 1 ] == "s" )
                                    $adder = substr( $adder, 0, -1 );
                            }
                            //echo "$adder";
                            if ( ! method_exists ( $entity, $adder ) )
                                continue;
                                                          
                            if ( is_object ( $object ) )
                            {
                                // simply add him
                                $entity -> $adder ( $object );  
                            }
                            elseif ( is_int ( $object ) )
                            {
                                // find him
                                $related_dao = $this -> dao -> related ( $key );
                                if ( ! ( $mapped_entity = $related_dao -> find ( $object ) ) )
                                    continue; // throw exception ?
                                $entity -> $adder ( $mapped_entity );

                            }
                            elseif ( is_array ( $object ) )
                            {
                                // call the model to create it
                            }
                            elseif ( is_int ( $id ) )
                            {
                                // try to find by $id
                                $related_dao = $this -> dao -> related ( $id );
                                if ( ! ( $mapped_entity = $related_dao -> find ( $entity_array [ $key ] ) ) )
                                    continue; // throw exception ?
                                $entity -> $adder ( $mapped_entity );
                            }
                        }
                    }   
                    else
                        ;// probably throw exception ?
                }
            }
            elseif ( $mapping [ "type" ] == $this -> MANYTOONE )
            {
                if ( array_key_exists ( $key, $entity_array ) )
                {
                    if ( is_int ( $entity_array [ $key ] ) || is_string ( $entity_array [ $key ] ) )
                    {
                        $entity_array [ $key ] = intval ( $entity_array [ $key ] );
                        // create new entity
                        $related_dao = $this -> dao -> related ( $key );
                        $mapped_entity_name = $related_dao -> getClassMetadata () -> name;;
                        if ( ! ( $mapped_entity = $related_dao -> find ( $entity_array [ $key ] ) ) )
                            $mapped_entity = new $mapped_entity_name;
                            // rather throw exception
                        $entity -> $key = $mapped_entity;
                    }
                    elseif ( is_array ( $entity_array [ $key ] ) )
                    {
                        // call the model on it
                    }
                    elseif ( is_object ( $entity_array [ $key ] ) )
                        $entity -> $key = $entity_array [ $key ];
                }
            }
            elseif ( $mapping [ "type" ] == $this -> ONETOONE )
            {

            }
            elseif ( $mapping [ "type" ] == $this -> MANYTOMANY )
            {

            }
        }

        return $entity;
    }


    public function update_array ( $entity, $from_array = array () )
    {
        $meta = $this -> dao -> getClassMetadata ();

        //print_r ( $meta );

        if ( is_object ( $entity ) && $entity instanceof Tulinkry\Model\Doctrine\Entity\BaseEntity )
            ;
        else
        {
            if ( ! ( $entity = $this -> item ( $entity ) ) )
                throw new NoResultException ( "Item not found" );
        }

        foreach ( $meta -> associationMappings as $key => $mapping )
        {
            if ( ! array_key_exists ( $key, $from_array ) )
                continue;

            if ( $mapping [ "type" ] == $this -> ONETOMANY )
            {
                $adder = "add" . ucfirst ( $key );
                if ( ! method_exists ( $entity, $adder ) )
                    if ( $adder [ strlen ( $adder ) - 1 ] == "s" )
                        $adder = substr( $adder, 0, -1 );                
                if ( is_array ( $from_array [ $key ] ) )
                {
                    foreach ( $from_array [ $key ] as $i => $val )
                        if ( $from_array [ $key ] instanceof Entity\BaseEntity )
                            $entity -> $adder ( $from_array [ $key ] );
                        else
                        {
                            $related_dao = $this -> dao -> related ( $key );
                            if ( ! ( $e = $related_dao -> find ( $val ) ) )
                                throw new NoResultException ( "Item not found" );
                            $entity -> $adder ( $e );
                        }
                }
                elseif ( $from_array [ $key ] instanceof Entity\BaseEntity )
                {
                    $entity -> $adder ( $from_array [ $key ] );
                }
                elseif ( ! is_object ( $from_array [ $key ] ) )
                {
                    $related_dao = $this -> dao -> related ( $key );
                    if ( ! ( $e = $related_dao -> find ( $from_array [ $key ] ) ) )
                        throw new NoResultException ( "Item not found" );
                    $entity -> $adder ( $e );
                }
            }
            elseif ( $mapping [ "type" ] == $this -> MANYTOONE )
            {
                $related_dao = $this -> dao -> related ( $key );
                if ( ! ( $e = $related_dao -> find ( $from_array [ $key ] ) ) )
                    throw new NoResultException ( "Item not found" );

                if ( array_key_exists ( "inversedBy", $mapping ) && $mapping [ "inversedBy" ] != "" )
                {
                    // use invesed side to call adder or remover
                    $from_array [ $key ] = $e;
                    $adder = "add" . ucfirst ( $mapping [ "inversedBy" ] );
                    $remover = "remove" . ucfirst ( $mapping [ "inversedBy" ] );
                    if ( ! method_exists ( $e, $adder ) )
                        if ( $adder [ strlen ( $adder ) - 1 ] == "s" )
                            $adder = substr( $adder, 0, -1 );
                    if ( ! method_exists ( $e, $remover ) )
                        if ( $remover [ strlen ( $remover ) - 1 ] == "s" )
                            $remover = substr( $remover, 0, -1 );
                    //echo "$adder";
                    //echo $remover;
                    if ( ! method_exists ( $e, $adder ) )
                        continue;                

                    if ( $entity -> $key && ! method_exists ( $entity -> $key, $remover ) )
                        continue;

                    if ( $entity -> $key )
                        $entity -> $key -> $remover ( $entity );
                    $e -> $adder ( $entity );
                }
                else
                {
                    // directly set
                    $setter = "set" . ucfirst ( $mapping [ "fieldName" ] );
                    $entity -> $setter ( $e );
                }
            }
            elseif ( $mapping [ "type" ] == $this -> ONETOONE )
            {


            }
            elseif ( $mapping [ "type" ] == $this -> MANYTOMANY )
            {
            }
        }

        foreach ( $meta -> fieldMappings as $key => $column )
        {
            $setter = "set" . Strings::firstUpper ( $key );
            if ( array_key_exists ( $key, $from_array ) && method_exists ( $entity, $setter ) )
            {
                $entity -> $setter ( $from_array [ $key ] );
            }
        }
        return $this -> update ( $entity );
    }

}