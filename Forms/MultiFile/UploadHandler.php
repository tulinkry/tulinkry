<?php

use Nette\Utils\Finder;

/**
 * @todo fake delete (delete to global trash)
 */
class UploadManager extends \Nette\Object
{
    /** @var string upload dir */
    private $uploadDir;

    /** @var string */
    private $thumbnailDir = 'thumbnail/';

    /** @param string $path */
    public function __construct($path)
    {
        $this->setUploadDir($path);
    }

    /** @return string */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /** @return string */
    public function getThumbnailDir()
    {
        return $this->uploadDir . $this->thumbnailDir;
    }

    /**
     * @param string $path
     * @throws InvalidArgumentException
     */
    public function setUploadDir($path)
    {
        // path does not end with slash
        if( !preg_match('#^[\w/]+/$#', $path )) {
            $path .= '/';
        }

        if(!is_dir($path)) {
            if( ! mkdir($path) )
                throw new \InvalidArgumentException("$path does not exists and dir cannot be create.");
        }
        if(!is_dir($path. $this->thumbnailDir) ) {
            if( ! mkdir($path . $this->thumbnailDir))
                throw new \InvalidArgumentException("$path does not exists and dir cannot be create.");
        }
        $this->uploadDir = $path;
    }

    /** @return array */
    public function getFiles()
    {
        $files = array();
        foreach (Finder::findFiles('*.jpg', '*.png', '*.pdf')->in($this->uploadDir) as $file) {
            $files[] = array(
                'name' => $file->getBasename(),
                'size' => $file->getSize(),
                'url' => '/concreteostrich/files/'.$file->getBasename(),
                'thumbnail_url' => '/concreteostrich/files/thumbnail/'.$file->getBasename(),
            );
        }
        return $files;
    }

    /**
     * @param array|\Nette\Http\FileUpload file or files to save
     * @return array
     */
    public function save($files)
    {
        $uploadedFiles = array();

        switch( gettype($files) ) {
            case 'object':
                $uploadedFiles[] = $this->saveOne($files);
                break;
            case 'array':
                foreach($files as $file) {
                    $uploadedFiles[] = $this->saveOne($file);
                }
                break;
            default:
                throw new \Nette\InvalidArgumentException();
                break;
        }

        return $uploadedFiles;
    }

    /** @param null $files */
    public function delete($files = null)
    {
        switch( gettype($files) ) {
            case 'string':
                $this->deleteOne($files);
                break;
            case 'array':
                foreach($files as $file) {
                    $this->deleteOne($file);
                }
                break;
            default:
                $this->deleteAll();
                break;
        }
    }

    /**
     * Save one file to the uploadDir
     *
     * @param Nette\Http\FileUpload $file
     * @return array info about file
     */
    private function saveOne(\Nette\Http\FileUpload $file)
    {
        $name = $file->getSanitizedName();
        $path = $this->uploadDir . $name;
        $file->move($path);
        $img = \Nette\Image::fromFile($path);
        $img->resize(80, 80)->save($this->uploadDir . 'thumbnail/' . $name);

        return array(
            'name' => $name,
            'size' => filesize($path),
            'url' => '/concreteostrich/files/' . $name,
            'thumbnail_url' => '/concreteostrich/files/thumbnail/'.$name,
        );
    }

    /**
     * Delete one file in uploadDir
     *
     * @param string $name
     */
    private function deleteOne($name)
    {
        if( file_exists($this->uploadDir . $name) ) {
            unlink($this->uploadDir . $name);
        }
        if( file_exists($this->uploadDir . $this->thumbnailDir . $name) ) {
            unlink($this->uploadDir . $this->thumbnailDir . $name);
        }
    }

    /**
     * Delete all file with sufixes: jpg, png, pdf in given directory tree (recursivly)
     */
    private function deleteAll()
    {
        foreach (Finder::findFiles('*.jpg', '*.png', '*.pdf')->from($this->uploadDir) as $file) {
            unlink( $file->getRealPath() );
        }
    }
}