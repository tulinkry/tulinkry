<?php

namespace Tulinkry\Model;


interface IBaseModel
{

	const FLUSH = 1;
	const NO_FLUSH = 0;

	public function all ();
	public function by ( $by = array (), $order = array () );
	public function limit ( $limit = 40, $offset = 0, $by = array (), $order = array () );
	public function insert ( $entity, $transactional = self::FLUSH );
	public function update ( $entity );
	public function remove ( $entity, $transactional = self::FLUSH );
	public function item ( $id );
	public function one ( $by = array () );
	public function count ( $by = array () );
	public function lastId ();


}