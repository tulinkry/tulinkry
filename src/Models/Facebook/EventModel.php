<?php


namespace Tulinkry\Model\Facebook;

use Kdyby\Facebook\Facebook;

class EventModel extends BaseModel
{

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $events = "events";

    /**
     * @var string
     */
    protected $event = "";

   	protected $fields = array (
   		"event" => [
   			"fields" => 
		   		  "attending_count,cover,declined_count,description,end_time,feed_targeting,id,invited_count,is_date_only,
		   		  location,maybe_count,name,noreply_count,owner,parent_group,privacy,start_time,timezone,ticket_uri,updated_time,venue",
	   	],
	   	"events" => [
	   		"fields" => 
	   			"cover,location,end_time,description,feed_targeting,is_date_only,name,owner,id,parent_group,
	   			privacy,start_time,ticket_uri,timezone,updated_time,venue"
	   	]
   	);



	public function all ( $user )
	{
		return $this -> by ( $user, [ "since" => 342000 ] );
	}



}