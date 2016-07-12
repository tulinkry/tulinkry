<?php


namespace Tulinkry\Model\Facebook;

use Kdyby\Facebook\Facebook;
use Kdyby\Facebook\FacebookApiException;
use Nette\Object;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class BaseModel implements IBaseModel
{

    /**
     * @var Facebook
     */
    protected $facebook;



    public function __construct(Facebook $facebook)
    {
        $this->facebook = $facebook;
    }


	/**
	 *
	 */
	public function all ( $user )
	{
		throw new NotImplementedException;
	}



	/**
	 *
	 */
	public function item ( $user, $id )
	{
		if ( $this -> event )
			$addr = "/" . $this -> event . "/" . $id;
		else
			$addr = "/". $id;
		$url = $addr;
		if ( array_key_exists ( "event", $this -> fields ) && count ( $this -> fields [ "event" ] ) )
			$url .= "?" . http_build_query ( $this -> fields [ "event" ] );

        try {
            // when you're loading a lot of event, it's better to use ->iterate() instead of several ->get()'s
            $event = $this->facebook->api( $url );
        } catch (FacebookApiException $ex) {
            Debugger::log($ex->getMessage(), 'facebook');

            return array();
        }	


        if ( array_key_exists ( "data", $event ) )
        	return $event [ "data" ];
        
        return $event;
    }

	public function by ( $user, $by = array (), $order = array () )
	{
		$this -> checkUser ( $user );
		
	
		if ( $this -> events )
			$addr = "/". $user . "/" . $this -> events;
		else
			$addr = "/". $user;

		$url = $addr;
		if ( array_key_exists ( "events", $this -> fields ) )
			$url .= "?" . http_build_query ( array_merge ( $by, $this -> fields [ "events" ] ) );

        try {
            // when you're loading a lot of events, it's better to use ->iterate() instead of several ->get()'s
            $events = $this->facebook->api( $url );
        } catch (FacebookApiException $ex) {
            Debugger::log($ex->getMessage(), 'facebook');

            return array();
        }		
        if ( ! array_key_exists ( "data", $events ) )
        	return [];
        
        return $events [ "data" ];
	}

	/**
	 *
	 */
	public function limit ( $user, $limit = 40, $offset = 0, $by = array (), $order = array () )
	{
		$by [ "limit" ] = $limit;
		$events = $this -> by ( $user, $by, $order );
		$r = [];
		$i = 0;
		foreach ( $events as $event )
		{
			
			if ( $offset * $limit <= $i && ( $offset + 1 ) * $limit > $i)
				$r [] = $event;
			$i ++;
		}
		return $r;
	}
	


	/**
	 *
	 */
	public function count ( $user, $by = array () )
	{
		return count ( $this -> by ( $user, $by ) );
	}

	/**
	 *
	 */
	public function one ( $user, $by = array () )
	{
		$events = $this -> by ( $user, $by );
		if ( count ( $events ) )
			return $events [ 0 ];
		return [];
	}

	/**
	 *
	 */
	public function insert ( $user, $entity )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function update ( $user, $entity, $transactional = FALSE )
	{
		throw new NotImplementedException;
	}

	/**
	 *
	 */
	public function remove ( $user, $entity, $transactional = FALSE )
	{
		throw new NotImplementedException;
	}


	protected function checkUser ( $user )
	{
		if ( ! $user )
			throw Nette\InvalidArgumentException ( "Facebook user looks empy '$user'. Can not continue." );
	}

}
