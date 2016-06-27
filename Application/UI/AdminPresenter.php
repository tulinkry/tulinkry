<?php

namespace Tulinkry\Application\UI;

use Nette;
use Nette\Security\IUserStorage;
/**
 * Base presenter for all application presenters.
 */
abstract class AdminPresenter extends \FrontModule\Presenters\BasePresenter
{

	public function startup ()
	{

		parent::startup ();
	    
		$user = $this -> user;
	    if (!$user->isLoggedIn()) 
	    {
	        if ($user->getLogoutReason() == IUserStorage::INACTIVITY) 
	        {
	            $this->flashMessage("Byl jsi odhlášen, protože jsi nebyl dlouho aktivní.", "error");
	        }
	        $this->flashMessage("Pro vstup do této části webu se musíš přihlásit.", "warning");
	        $this->redirect(":Front:Sign:login", array("backlink" => $this->storeRequest()));
	    }
	}

}
