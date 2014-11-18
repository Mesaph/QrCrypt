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
     * @throws SerializationException
     * @return string
     */
    public function toString();

    /**
     * Returns the unique identifier for this mask. This identifier has to be exactly 5 characters long.
     * This method should return an unique identifier that is only used for this mask.
     * @return string
     */
    public function getId();

} 