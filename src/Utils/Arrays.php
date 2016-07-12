<?php


namespace Tulinkry\Utils;

use Nette;

class Arrays extends Nette\Object
{
	public static function createSequence ( $start = 0, $end = 10, $step = 1, $withIndexes = true )
	{
		$seq = [];
		for ( $i = $start; $i <= $end; $i += $step )
			if ( $withIndexes ) $seq [ $i ] = $i;
			else $seq [] = $i;
		return $seq;
	}
}