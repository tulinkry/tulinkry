<?php


namespace Tulinkry\Model;

use Exception;
use Nette\Object;

class NotImplementedException extends Exception
{
	public function __construct ()
	{
		parent::__construct ( "Not implemented." );
	}
}


class BaseModel extends Object implements IBaseModel
{

	/**
	 *
	 */
	public function all ()
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function by ( $by = array (), $order = array () )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function insert ( $entity, $transactional = self::FLUSH )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function update ( $entity )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function remove ( $entity, $transactional = self::FLUSH )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function item ( $id )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function count ( $by = array () )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function lastId ()
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function limit ( $limit = 40, $offset = 0, $by = array (), $order = array () )
	{
		throw new NotImplementedException;
	}
	
	/**
	 *
	 */
	public function one ( $by = array () )
	{
		throw new NotImplementedException;
	}
	
}