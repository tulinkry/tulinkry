<?php

namespace Tulinkry\Forms;

use Tulinkry\Application\UI;
use Tulinkry\Exception;
use Tulinkry\Mail\Message;

class ContactForm extends UI\Form
{
	protected $site;
	protected $recipients = array ();

	public $flashText = "Zpráva byla úspěšně odeslána.";
	public $subjectText = "Kontaktní formulář";
	public $includeParams = true;

	public function __construct ( $emails = array (), $site = "Concrete Ostrich" )
	{
		parent::__construct ();

		if ( ! is_array ( $emails ) )
			$emails = array ( $emails );

		if ( ! count ( $emails ) )
			throw new Exception ( "No recipients given." );

		$this -> site = $site;
		$this -> recipients = $emails;

		$this -> addText ( "name", "Jméno" )
			  -> setAttribute ( "placeholder", "Vaše jméno" )
			  -> addRule(UI\Form::FILLED, "Zadejte vaše jméno" )
			  -> setRequired ();

		$this -> addEmail ( "email", "Email" )
			  -> addRule(UI\Form::FILLED, "Zadejte váš email" )
			  -> setRequired ();

		$this -> addTextArea ( "message", "Zpráva" )
			  -> addRule(UI\Form::FILLED, "Zadejte zprávu" );

		
		$this -> addSubmit ( "submit", "Odeslat" );
	}


	public function process ( $form )
	{
		$values = $form -> values;

		// message
		$message = "Dobrý den,\n\nze stránky ".$this->site." Vám byla zaslána následující zpráva:\n\n"
			. $values["message"];

		// all params
		if ($this->includeParams) {
			$message .= "\n\nVeškeré parametry:\n";

			foreach ($form->components as $key => $component) {
				if($key != "submit" && $key != "do") {
					$message .= $component->caption . ": " . $component->value . "\n";
				}
			}
		}

		if (!isset($values["email"])) {
			$values["email"] = "no-reply@" . $this->site;
		}

		$subject = rtrim($this->site.  " - ". $this->subjectText, " - ");

		$mail = new Message;
		$mail->setFrom($values["email"])
			->setBody($message)
			->setSubject($subject);


		foreach ($this->recipients as $value)
			$mail->addTo($value);

		$mail->send();

		$this -> presenter -> flashMessage( $this -> flashText, "success" );
		$this -> presenter -> redirect( "this" );		


	}

}