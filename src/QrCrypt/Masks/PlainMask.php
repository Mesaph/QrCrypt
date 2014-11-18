<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt\Masks;
use Tart\QrCrypt\Mask;


class PlainMask implements Mask {

    private $string = '';

    public function setString($string) {
        $this->string = $string;
    }

    public function getString() {
        return $this->string;
    }

    public function toString()
    {
        return $this->string;
    }

    public function getId()
    {
        return 'plain';
    }

} 