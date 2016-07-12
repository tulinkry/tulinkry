<?php

namespace Tulinkry\Mail;

use Nette;
use Tulinkry;

class MailReader extends Nette\Object
{
	protected $stream;

	private $server;
	private $user;
	private $pass;
	private $port;
	private $arguments;

	protected $inbox = array ();
	protected $message_count;


	public function __construct ( $server, $port = NULL, $arg = NULL, $user = NULL, $pass = NULL )
	{
		if ( is_array ( $server ) ) {
			$this -> server = $server [ 'server' ];
			$this -> user = $server [ 'user' ];
			$this -> pass = $server [ 'password' ];
			$this -> port = $server [ 'port' ];
			$this -> arguments = $server [ 'arg' ];
		} elseif ( is_object ( $server ) ) {
			$this -> server = $server -> server;
			$this -> user = $server -> user;
			$this -> pass = $server -> password;
			$this -> port = $server -> port;
			$this -> arguments = $server -> arg;
		} else {
			$this -> server = $server;
			$this -> user = $user;
			$this -> pass = $pass;
			$this -> port = $port;
			$this -> arguments = $arg;
		}

		$this -> connect ();
	}

	public function __destruct ()
	{
		$this -> deconnect ();
	}

	public function count ()
	{
		return $this -> message_count;
	}

	public function read ( $id )
	{
		return @imap_setflag_full ( $this -> stream, $id, "\\Seen" );
	}

	public function unread ( $id )
	{
		return @imap_clearflag_full ( $this -> stream, $id, "\\Seen" );
	}

	public function by ( $criteria, $limit = 50, $offset = 1 )
	{
		if ( $offset <= 0 ) {
			$offset = 1;
		}

		if ( $offset + $limit > $this -> message_count ) {
			$limit = $this -> message_count - $offset + 1;
		}

		$messages = imap_search ( $this -> stream, $criteria );
		if ( $messages === FALSE )
			return [];

		$incomming = [];
		foreach ( $messages as $messageid ) {
			$incomming [ $messageid ] = $this -> prepareMessage ( $messageid );
		}

		$this -> inbox = array_merge ( $this -> inbox, $incomming );

		rsort ( $incomming );
		return $incomming;
	}


	public function limit ( $limit = 50, $offset = 1 )
	{
		if ( $offset <= 0 ) {
			$offset = 1;
		}

		if ( $offset + $limit > $this -> message_count ) {
			$limit = $this -> message_count - $offset + 1;
		}


		$incomming = [];
		for ( $i = $offset; $i < $limit + 1; $i ++ ) {
			$incomming [ $i ] = $this -> prepareMessage ( $i );
		}

		$this -> inbox = array_merge ( $this -> inbox, $incomming );
		rsort ( $incomming );
		return $incomming;
	}

	protected function prepareMessage ( $id )
	{
		$header = imap_header ( $this -> stream, $id );
		$structure = imap_fetchstructure($this->stream,$id);
		$date = Tulinkry\DateTime::createFromFormat( "U", $header -> udate );

		$msg = new \StdClass;
		$msg -> id = $id;
		$msg -> subject = self::decodeImapMime($header -> subject);
		/*
			arrays of objects with
				-> personal
				-> adl
				-> mailbox
				-> host
		*/
		$msg -> to = $header -> to;
		$msg -> from = $header -> from;
		$msg -> cc = isset($header -> cc) ? $header -> cc : [];
		$msg -> bcc = isset($header -> bcc) ? $header -> bcc : [];

		foreach ( [ 'to', 'from', 'cc', 'bcc' ] as $array ) {
			if ( isset($msg->$array) ) {
				foreach ( $msg->$array as $person ) {
					if ( isset($person->personal) ) {
						$person->personal = self::decodeImapMime ($person->personal);
					}
				}
			}
		}

		$msg -> size = $header -> Size;
		$msg -> seen = $header -> Unseen == "U" ? false : true;
		$msg -> date = $date;

		
	    // BODY
		$message = new \StdClass;
		$message -> id = $id;
		$message -> html = $message -> plain = $message -> charset = '';
		$message -> attachments = array ();


	    
	    if (!isset($structure->parts))  // simple
	        $this->prepareMessagePart($message,$structure,0);  // pass 0 as part-number
	    else {  // multipart: cycle through each part
	        foreach ($structure->parts as $partno0=>$p)
	            $this->prepareMessagePart($message,$p,$partno0+1);
	    }

	    $message -> plain = trim ( $message -> plain );
	    $message -> html = preg_replace('/(<br( \/)?>)*$/', '', $message -> html );
		$message -> old_charset = $message -> charset;
		$message -> charset = "utf-8";

		$msg -> message = $message;

		return $msg;
	}

	protected function prepareMessagePart(&$message,$p,$partno) {
	    // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple

	    // source: http://php.net/manual/en/function.imap-fetchstructure.php

	    // DECODE DATA
	    $data = ($partno)?
	        imap_fetchbody($this->stream,$message->id,$partno, FT_PEEK):  // multipart
	        imap_body($this->stream,$message->id, FT_PEEK);  // simple
	    // Any part may be encoded, even plain text messages, so check everything.
	    if ($p->encoding==4)
	        $data = quoted_printable_decode($data);
	    elseif ($p->encoding==3)
	        $data = base64_decode($data);

	    // PARAMETERS
	    // get all parameters, like charset, filenames of attachments, etc.
	    $params = array();
	    if (isset($p->parameters))
	        foreach ($p->parameters as $x)
	            $params[strtolower($x->attribute)] = $x->value;
	    if (isset($p->dparameters))
	        foreach ($p->dparameters as $x)
	            $params[strtolower($x->attribute)] = $x->value;

	    // ATTACHMENT
	    // Any part with a filename is an attachment,
	    // so an attached text file (type 0) is not mistaken as the message.
	    if (isset($params['filename']) || isset($params['name'])) {
	        // filename may be given as 'Filename' or 'Name' or both
	        $filename = ($params['filename'])? $params['filename'] : $params['name'];
	        // filename may be encoded, so see imap_mime_header_decode()
	        $message->attachments[$filename] = $data;  // this is a problem if two files have same name
	    }

	    // TEXT
	    if ($p->type==0 && $data) {
	        // Messages may be split in different parts because of inline attachments,
	        // so append parts together with blank row.
	        $message->charset = $params['charset'];  // assume all parts are same charset
	        
	        if (strtolower($p->subtype)=='plain')
	            $message->plain .= self::decodeImapBody(trim($data), $message->charset) ."\n\n";
	        else
	            $message->html .= self::decodeImapBody($data, $message->charset) ."<br><br>";
	    }

	    // EMBEDDED MESSAGE
	    // Many bounce notifications embed the original message as type 2,
	    // but AOL uses type 1 (multipart), which is not handled here.
	    // There are no PHP functions to parse embedded messages,
	    // so this just appends the raw source to the main message.
	    elseif ($p->type==2 && $data) {
	        $message->plain .= $data."\n\n";
	    }

	    // SUBPART RECURSION
	    if (isset($p->parts)) {
	        foreach ($p->parts as $partno0=>$p2)
	            $this->prepareMessagePart($message->id,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
	    }
	}


	static public function decodeImapBody ( $body, $charset = '' )
	{
		return iconv ( $charset, "UTF-8", quoted_printable_decode ( $body ) );
	}

	static public function decodeImapMime ($str)
	{
		$result = "";
		$decode_header = imap_mime_header_decode ( $str );
		foreach ($decode_header AS $obj) {
			if ( $obj -> charset == 'default' ) {
				$result .= htmlspecialchars ( $obj -> text );
				continue;
			}
			$result .= htmlspecialchars( iconv($obj->charset, 'UTF-8', quoted_printable_decode ( rtrim( $obj->text, "\t" ) ) ) );
		}

		return $result;
	}

	protected function connect ()
	{
		$server = '{' . $this -> server;
		$server .= $this -> port ? ':' . $this -> port : '';
		$server .= $this -> arguments ? '/' . $this -> arguments : '';
		$server .= '}INBOX';

		$this -> stream = @imap_open ( $server, $this -> user, $this -> pass );
		if ( ! $this -> stream ) {
			throw new \Exception ( imap_last_error () );
		}

		$this -> message_count = imap_num_msg($this -> stream);
	}


	protected function deconnect ()
	{
		$this -> inbox = array ();
		$this -> message_count = 0;
		@imap_close ( $this -> stream );
	}

};