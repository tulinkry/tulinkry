<?php
/**
 * @author Kryštof Tulinger
 *
 */

namespace Tulinkry\Model\Doctrine;

use Kdyby\Doctrine\EntityManager;
use Tulinkry;

interface IBaseModel extends Tulinkry\Model\IBaseModel
{

    public function flush ();
	public function refresh ( $entity );
	public function detach ( $entity );
	public function merge ( $entity );
    public function create ( $entity_array = array () );

}