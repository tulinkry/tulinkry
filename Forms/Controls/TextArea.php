<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Tulinkry\Forms\Controls;

use Nette;


/**
 * Multiline text input control.
 *
 * @author     David Grudl
 */
class TextArea extends Nette\Forms\Controls\TextArea
{

    public function __construct($label = NULL, $cols = NULL, $rows = NULL)
    {
        parent::__construct($label, $cols, $rows);
        $this -> setAttribute ( "class", "form-control" );
    }

}
