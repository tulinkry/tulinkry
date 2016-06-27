<?php

namespace Tulinkry\GitModule;

use Nette;
use Tulinkry\Zip\ZipArchiver;

class GitPresenter extends Nette\Application\UI\Presenter
{
	const BRANCH_NAME = 'master';
	const DOWNLOAD_NAME = 'master.zip';
	const USERNAME = 'tulinkry';
	const REPOSITORY = 'bigbandbiskupska';
	protected $DOWNLOAD_URL = NULL;
	

	/** @var Tulinkry\Services\ParameterService @inject */
	//public $params;

	/** @var Nette\Caching\IStorage @inject */
	public $cache;

	public function startup ()
	{
		parent::startup();
		$this->DOWNLOAD_URL = "https://github.com/".self::USERNAME."/".self::REPOSITORY."/archive/master.zip";
	}

	public function actionDefault ()
	{
		print_r($_POST);
		/*if ( ( $content = file_get_contents($this->DOWNLOAD_URL) ) === FALSE )
			throw new \Exception("Invalid input file");

		$file = APP_DIR . "/../". self::DOWNLOAD_NAME;

		if ( file_put_contents($file, $content) === FALSE )
			throw new \Exception("Cannot save data");

		$zip = new ZipArchiver;
		
		$this->template->status = "failed";

		
		$res = $zip->open ( $file );
		if ($res === TRUE) {
			$zip->extractSubdirTo(APP_DIR . "/../", self::REPOSITORY . "-" . self::BRANCH_NAME);
			$zip->close();
			$this->cache->clean(array(\Nette\Caching\Cache::ALL => true));

			$this->template->status = "saved";
		} else {
			throw new \Exception("Cannot extract");
		}*/
            $this->template->status = 'processed';
	}
	
}