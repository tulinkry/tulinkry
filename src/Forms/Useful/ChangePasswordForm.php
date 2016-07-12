<?php

namespace Tulinkry\Forms;

use Tulinkry\Application\UI;
use Authenticator\Authenticator;
use Kdyby\Doctrine\DBALException;

class ChangePasswordForm extends UI\Form
{
	protected $model;
	protected $user;

	public function __construct ( $model, $user )
	{

		$this -> model = $model;
		$this -> user = $user;

		//$items = array ( 0 => "Modrý", 1 => "Zelený", 2 => "Růžový" );
		//$form -> addSelect ( "skin", "Skin", $items );

		$this -> addPassword ( "password1", "Nové heslo" )
			  -> addRule ( Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6 );
		$this -> addPassword ( "password2", "Znovu nové heslo" )
			  -> addRule ( Form::EQUAL, "Hesla se musí shodovat", $this [ "password1" ] );

		$this -> addPassword ( "old_password", "Staré heslo" )
			  -> addRule ( Form::FILLED, "Musíte zadat své staré heslo" )
			  -> setRequired ( "Zadejte staré heslo" );

		$this -> addSubmit ( "submit", "Uložit" );

	}

	public function process ( $form )
	{
		$values = $form -> values;

		//$this -> userClass -> setSkin ( $values [ "skin" ] );

		$verifyPass = Authenticator::calculateHash ( $values [ "old_password" ], $this -> user -> email );
		if ( $verifyPass !== $this -> user -> password )
		{
			$form -> addError ( "Vaše staré heslo bylo zadáno nesprávně!" );
			return;
		}

		$this -> user -> setPassword ( Authenticator::calculateHash ( $values [ "password1" ], $this -> user -> email ) );

		try {
			$this -> model -> update ( $this -> user );
		}
		catch ( \Exception $e )
		{
			$form -> addError ( "Bohužel nejde uložit." );
			return;
		}
		$this -> presenter -> flashMessage ( "Změny byly uloženy" );
		$this -> presenter -> redirect ( "this" );

	}

}


