<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt\Masks;
use Tart\QrCrypt\Mask;
use Tart\QrCrypt\Exceptions\MissingInformationException;


class TransactionMask implements Mask {

    public function toString()
    {
        throw new MissingInformationException();
    }

    public function getId()
    {
        return 'trans';
    }

} 