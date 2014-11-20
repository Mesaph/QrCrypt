<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt;

/**
 * Interface Mask
 * This interface can be implemented to create a concrete mask that can be used by the QrCrypt class. An implementation
 * should provide some means to fill the mask with data and encode this data using the toString() method.
 *
 * @package Tart\QrCrypt
 */
interface Mask {

    /**
     * Serializes the information contained in this mask into a string.
     * This method should generate the string which will be encoded into the qr-code. Note that we chose not to use
     * __toString() to allow for exceptions to be thrown from
     * this method.
     *
     * @return string
     */
    public function toString();

    /**
     * Returns the unique identifier for this mask.
     * This method should return an unique identifier that is only used for this mask. It can be up to 5 characters long
     * and must not contain a colon.
     * @return string
     */
    public function getId();

} 