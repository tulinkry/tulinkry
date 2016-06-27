<?php

namespace Tulinkry\Forms;

use Tulinkry\Application\UI;
use Tulinkry\Exception;

class RegistrationForm extends Form
{

	public function __construct ()
	{
		$this->addText('username', 'Přihlašovací jméno:')
			-> setAttribute ( "placeholder", "Přihlašovací jméno" )
			-> addRule ( UI\Form::MIN_LENGTH, "Minimální délka přihlašovacího jména jsou %d znaky", 3 )
			-> addRule ( UI\Form::PATTERN, "Jméno může obsahovat pouze povolené znaky: [a-zA-Z0-9]{}()™@#$+*-_:?!^~", "[a-zA-Z0-9ěščřžýáíéóúůďťňĎŇŤŠČŘŽÝÁÍÉÚŮ\\$\*\+\{\}\(\)\-\_\!\?\:\~\^\#\@\™]{3,}" )
			-> setRequired('vyplňte uživatelské jméno');

		$this->addPassword('password', 'Heslo:')
			-> addRule ( UI\Form::MIN_LENGTH, "Minimální délka vašeho hesla jsou %d znaků", 6 )
			-> setAttribute ( "placeholder", "*****" )
			->setRequired('Vyplňte heslo');

		$this->addPassword('another_password', 'Heslo znovu:')
			-> setAttribute ( "placeholder", "*****" )
			->setRequired('Zopakujte heslo pro kontrolu');

		$this->addText('email', 'Email:')
			-> setAttribute ( "placeholder", "váš@email.com" )
			->setRequired('Vyplňte vaší emailovou adresu');
    
  		$this->addSubmit('register', 'Registrovat')
			->setAttribute ( "class", "btn btn-primary" );

		$this -> onValidate [] = array ( $this, "validateForm" );

		$this -> getElementPrototype() -> addClass ( "form-signin" );
	}		

	public function validateForm($form)
	{
		$values = $form->getValues();

		/**
		 * ============>
		 * SOME STUNNING VALIDATION HERE
		 *
		 *
		 *  <===========
		 */

		if ( $values["password"] !== $values["another_password"] )
		{
			$form -> addError ( "Hesla musí být stejná." );
			return;
		}

	}
};