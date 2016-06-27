<?php


namespace Tulinkry\Utils;

use Nette;

class Gps extends Nette\Object
{
	/**
     * return distance in metres
     * @return float
     */
    public static function distance ( $lat1, $lng1, $lat2, $lng2 )
    {
        return acos(
            cos(deg2rad($lat1))*cos(deg2rad($lng1))*cos(deg2rad($lat2))*cos(deg2rad($lng2))
            + cos(deg2rad($lat1))*sin(deg2rad($lng1))*cos(deg2rad($lat2))*sin(deg2rad($lng2))
            + sin(deg2rad($lat1))*sin(deg2rad($lat2))
        ) * 6372.795 * 1000;        
    }
}