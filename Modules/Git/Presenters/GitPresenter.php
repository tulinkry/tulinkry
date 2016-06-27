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

	public function verifySignature ($body) {

		if(!isset($_SERVER["HTTP_X_HUB_SIGNATURE"]))
			return false;

		list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');

		if (!in_array($algo, hash_algos(), TRUE)) {
			$this->error("Hash algorithm '$algo' is not supported.");
		}

		return hash_equals($hash, hash_hmac($algo, $body, "9e94b15ed312fa42232fd87a55db0d39"));
	}

	public function recursiveRmDir($dir)
	{
	    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
	    foreach ($iterator as $filename => $fileInfo) {
	        if ($fileInfo->isDir()) {
	            rmdir($filename);
	        } else {
	            unlink($filename);
	        }
	    }
	}

	public function actionDefault ()
	{
		if(($postdata = file_get_contents("php://input")) === FALSE)
			$this->error("Cannot read input");

		if(($decoded = json_decode($postdata)) === FALSE)
			$this->error("Cannot decode json data");


		if(!$this->verifySignature($postdata))
			$this->error("Secret is needed to authenticate this request");

		if ( ( $content = file_get_contents($this->DOWNLOAD_URL) ) === FALSE )
			$this->error("Invalid input file");

		$file = APP_DIR . "/../". self::DOWNLOAD_NAME;

		if ( file_put_contents($file, $content) === FALSE )
			$this->error("Cannot save the data");

		$zip = new ZipArchiver;
		
		$this->template->status = "failed";
		
		$res = $zip->open ( $file );
		if ($res === TRUE) {
			$zip->extractSubdirTo(APP_DIR . "/../", self::REPOSITORY . "-" . self::BRANCH_NAME);
			$zip->close();
			//$this->cache->clean(array(Nette\Caching\Cache::ALL => true));
			// clear cache
			$this->recursiveRmDir(APP_DIR . "/../temp/cache");
			@mkdir(APP_DIR . "/../temp/cache");

			$this->template->status = "saved";
		} else {
			$this->error("cannot extract the zip archive");
		}
	}
	
}