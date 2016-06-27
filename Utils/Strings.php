<?php


namespace Tulinkry\Utils;

use Nette;

class Strings extends Nette\Utils\Strings
{


  static public function camelize ( $scored )
  {
    return lcfirst(
      implode(
        '',
        array_map(
          'ucfirst',
          array_map(
            'strtolower',
            explode(
              '_', $scored)))));
  }

  static public function decamelize ( $cameled ) 
  {
    return implode(
      '_',
      array_map(
        'strtolower',
        preg_split('/([A-Z]{1}[^A-Z]*)/', $cameled, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)));
  }


}
