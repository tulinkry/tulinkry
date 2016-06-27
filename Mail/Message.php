<?php

namespace Tulinkry\Mail;

use Nette;

class Message extends Nette\Mail\Message
{
	protected static $mailerTemplate = "Nette\Mail\SendmailMailer";


	public function send ()
	{
		$mailer = is_object( static::$mailerTemplate ) ? static::$mailerTemplate : new static::$mailerTemplate;

		$mailer -> send ( $this -> build () );
	}

}
