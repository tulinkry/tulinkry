<?php

namespace Tulinkry\Gallery;

interface IDataStorage
{
	public function getGalleries ();
	public function getFiles ( $gallery_id );

	public function getImages ( $gallery_id );

	public function getImage ( $image_id );

	public function getThumbnails ( $image_id );
	public function getThumbnail ( $image_id, $width, $height, $cache = true );

}