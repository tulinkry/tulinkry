<?php


namespace Tulinkry\Model\Facebook;


interface IBaseModel 
{

	public function all ( $user );
	public function by ( $user, $by = array (), $order = array () );
	public function insert ( $user, $entity );
	public function update ( $user, $entity );
	public function remove ( $user, $entity );
	public function item ( $user, $id );
	public function count ( $user, $by = array () );
	public function limit ( $user, $limit = 40, $offset = 0, $by = array (), $order = array () );
	public function one ( $user, $by = array () );

}
