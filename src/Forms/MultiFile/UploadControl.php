<?php

namespace Tulinkry;

use Nette\Application\UI\Form;
use Tulinkry;
use PDOException;

class UploadControl extends Tulinkry\Application\UI\Control
{
    public $onUpload = array ();

    protected $templates;
    protected $inline_file_types = '/\.(gif|jpe?g|png)$/i';
    protected $accept_file_types = '/.+$/i';

    protected $errors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image'
    );


    public function __construct ()
    {
        parent::__construct();
    }


    public function handleSignal()
    {
        set_time_limit(0);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
            case 'HEAD':
                //$this->head();
                break;
            case 'GET':
                $this->handleImages();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->handleUpload();
                break;
            case 'DELETE':

                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                break;
        }
    }

    public function getPayload ()
    {
        return $this -> presenter -> payload;
    }

    public function setPayload ( $value )
    {
        $this -> presenter -> payload -> $value;
        return $this;
    }

    public function sendPayload ()
    {
        return $this -> presenter -> sendPayload ();
    }


    public function addTemplate ( $template )
    {
        $this -> templates [] = $template;
    }

    public function addTemplateVariables ( $vars )
    {
        foreach ( $vars as $key => $var )
            $this -> template -> $key = $var;
    }

    public function get_config_bytes($val) 
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) 
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    protected function get_file_size( $file_path, $clear_stat_cache = false ) 
    {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return filesize($file_path);
    }

    protected function get_error_message($error) 
    {
        return array_key_exists($error, $this->errors) ?
            $this->errors[$error] : $error;
    }

    protected function isInvalid($error, $name) 
    {
        if ($error) 
        {
            return $this -> get_error_message ( $error );
        }


        $content_length = $_SERVER [ "CONTENT_LENGTH" ];
        $post_max_size = $this -> get_config_bytes ( ini_get ( "post_max_size" ) );
        if ( $post_max_size && ($content_length > $post_max_size))
        {
            return $this -> get_error_message ( "post_max_size" );
        }

        if ( ! preg_match( $this -> accept_file_types, $name ) ) 
        {
            return $this -> get_error_message ( "accept_file_types" );
        }
        if ( ! preg_match( $this -> inline_file_types, $name ) ) 
        {
            return $this -> get_error_message ( "inline_file_types" );
        }
        return NULL;
    }




    public function handleUpload()
    {
        $request = $this -> presenter -> context -> getByType('Nette\Http\IRequest');

        $files = $request -> getFiles ();
        $post = $request -> getPost ();
        $files = $files [ "files" ];

        //if ( count ( $files ) != count ( $galleries ) && count ( $galleries ) != count ( $names ) )
        //    throw new Exception ();

        $len = count ($files);
        for ( $i = 0; $i < $len; $i ++ )
        {
            $error = $files [ $i ] -> error ? $files [ $i ] -> error : null;
            $name = $files[$i]->getSanitizedName ();
            if ( $files [$i] -> isOk () && ! ( $errmsg = $this -> isInvalid ( $error, $name ) ) )
            {
                $data = [];
                foreach ( $post as $key => $val )
                    $data [ $key ] = $val [ $i ];

                try
                {
                    $response = NULL;
                    foreach ( $this -> onUpload as $uploadHandler )
                        if ( ( $second_response = $uploadHandler ( $files [ $i ], $data ) ) )
                            $response = $second_response;
                }
                catch ( \Exception $e )
                {
                    $this -> payload -> files [] = $this -> buildPayload ( $files[$i]->getSanitizedName(),
                                                                           $files[$i]->getSize(),
                                                                           null,
                                                                           null,
                                                                           "Obrázek se nepodařilo uložit do databáze. " . $e -> getMessage () );
                    continue;                    
                } 

                if ( ! $response )
                {
                    $this -> payload -> files [] = $this -> buildPayload ( $files[$i]->getSanitizedName(),
                                                                           $files[$i]->getSize(),
                                                                           null,
                                                                           null,
                                                                           "Obrázek se nepodařilo uložit." );  
                    continue;                    
                }
                    
                $this -> payload -> files [] = $this -> buildPayload ( $files[$i]->getSanitizedName(),
                                                                       $files[$i]->getSize(),
                                                                       $response -> path,
                                                                       $response -> thumbnail,
                                                                       null );
            }
            else
            {
                if ( isset ( $errmsg ) )
                {
                    $this -> payload -> files [] = $this -> buildPayload ( $files[$i]->getSanitizedName(),
                                                                           $files[$i]->getSize(),
                                                                           null,
                                                                           null,
                                                                           $errmsg );                    
                }
            }
        }
        //$this->sendPayload();
    }

    protected function buildPayload ( $name, $size, $url, $tUrl, $error )
    {
        $ret = array ( "name" => $name, "size" => $size, "url" => $url, "thumbnailUrl" => $tUrl );
        if ( $error )
            $ret = $ret + array ( "error" => $error );
        return $ret;
    }

    private function prepareImages($path)
    {

    }

    private function imagesFilter(array $files)
    {
        $images = array();
        foreach($files as $file) {
            if( preg_match('/\w*.jpg/', $file)) $images[] = $file;
            if( preg_match('/\w*.png/', $file)) $images[] = $file;
        }
        return $images;
    }

    public function render ()
    {
        $this -> template -> setFile ( __DIR__ . "/uploadControl.latte" );
 
        foreach ($this->templates as &$template) {
            if ($template instanceof IFileTemplate) {
                $template = $template->getFile();
            }
            if (!file_exists($template)) {
                throw new \RuntimeException("Cells template '{$template}' does not exist.");
            }
        }

        $this -> template -> templates = $this -> templates;

        $this -> template -> render ();
    }
}


