<?php


namespace Tulinkry\Model\Doctrine\Entity;

interface IBaseEntity
{
	public function toArray ();
    public function toSelect ( $collection );
    public function getDescription ();

};


