<?php


namespace Tulinkry\Model\Facebook;

use Kdyby\Facebook\Facebook;

class PostModel extends BaseModel
{

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $events = "feed";

    /**
     * @var string
     */
    protected $event = "";

    /**
     *	Used to gather special fields from facebook api
     * 	Typical is name "fields" => "string separated by commas"
     */
   	protected $fields = array (
   		"event" => [],
	   	"events" => []
   	);


	public function all ( $user )
	{
		$ret = $this -> by ( $user, [ "since" => 342000 ] );
        foreach ( $ret as $result )
        {
            if ( array_key_exists ( "type", $result ) && 
                 $result [ "type" ] === "photo" && 
                 array_key_exists ( "picture", $result ) &&
                 array_key_exists ( "object_id", $result ) )
            {
                $object = $this -> item ( $user, $result [ "object_id" ] );
                if ( $object && array_key_exists ( "source", $object ))
                {
                    $result [ "picture" ] = $object [ "source" ];
                }
            }
        }
        return $ret;	
    }
}