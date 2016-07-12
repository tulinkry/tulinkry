<?php

namespace Tulinkry\Utils;

class Date
{
	static public function weekday ( $timestamp, $localization = "cs", $format = 0 )
	{
		$weekdays = array (
			"cs" => array (
				1 => array ( "Pondělí", "Po" ),
				2 => array ( "Úterý", "Út" ),
				3 => array ( "Středa", "St" ),
				4 => array ( "Čtvrtek", "Čt" ),
				5 => array ( "Pátek", "Pá" ),
				6 => array ( "Sobota", "So" ),
				0 => array ( "Neděle", "Ne" )
			)
		);

		if ( isset ( $weekdays [ $localization ] [ date ( 'w', $timestamp ) ] [ $format ] ) )
		{
			return $weekdays [ $localization ] [ date ( 'w', $timestamp ) ] [ $format ];
		}

		return date ( "D", $timestamp );
	}
};