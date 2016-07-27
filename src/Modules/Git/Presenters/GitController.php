<?php

namespace Tulinkry\GitModule;

use Nette;
use Tulinkry\Zip\ZipArchiver;
use Tulinkry;

class GitController extends Nette\Application\UI\Presenter
{
	/** @var Tulinkry\GitModule\Services\ParameterService @inject */
	public $parameterService;

	/** @var Nette\Caching\IStorage @inject */
	public $cache;

	public function verifySignature ($body) {

		if(!isset($_SERVER["HTTP_X_HUB_SIGNATURE"]))
			return false;

		list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');

		if (!in_array($algo, hash_algos(), TRUE)) {
			$this->error("Hash algorithm '$algo' is not supported.");
		}

		return hash_equals($hash, hash_hmac($algo, $body, $this->parameterService->key));
	}

	public function recursiveRmdir($dir)
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

		if($this->parameterService->key && !$this->verifySignature($postdata))
			$this->error("Secret is needed to authenticate this request");

		$download_url = sprintf( "https://github.com/%s/%s/archive/%s",
											$this->parameterService->username,
											$this->parameterService->repository,
											$this->parameterService->file );

		if ( ( $content = file_get_contents($download_url) ) === FALSE )
			$this->error("Invalid input file");

		$file = APP_DIR . "/../". $this->parameterService->file;

		if ( file_put_contents($file, $content) === FALSE )
			$this->error("Cannot save the data");

		$zip = new ZipArchiver;
		
		$this->template->status = "failed";
		
		$res = $zip->open ( $file );
		if ($res === TRUE) {
			$zip->extractSubdirTo(APP_DIR . "/../", $this->parameterService->repository . "-" . $this->parameterService->branch);
			$zip->close();
			//$this->cache->clean(array(Nette\Caching\Cache::ALL => true));
			// clear cache
			$this->recursiveRmdir(APP_DIR . "/../temp/cache");
			@mkdir(APP_DIR . "/../temp/cache");

			$this->template->status = "saved";
		} else {
			$this->error("cannot extract the zip archive");
		}
	}
	
}