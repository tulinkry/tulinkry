<?php

namespace Tulinkry\Application\UI;

use Nette;
use Nette\Security\IUserStorage;

/**
 * Base presenter for all admin application presenters.
 */

trait AdminPresenterBody
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



if ( class_exists("\FrontModule\Presenters\BasePresenter") ) 
{
	abstract class AdminPresenter extends \FrontModule\Presenters\BasePresenter
	{
		use AdminPresenterBody;
	}
} 
else if ( class_exists("\App\FrontModule\Presenters\BasePresenter") )
{
	abstract class AdminPresenter extends \App\FrontModule\Presenters\BasePresenter
	{
		use AdminPresenterBody;
	}
}
else if ( class_exists("\App\Presenters\BasePresenter") )
{
	abstract class AdminPresenter extends \App\Presenters\BasePresenter
	{
		use AdminPresenterBody;
	}
}
else if ( class_exists("\Presenters\BasePresenter") )
{
	abstract class AdminPresenter extends \Presenters\BasePresenter
	{
		use AdminPresenterBody;
	}
}