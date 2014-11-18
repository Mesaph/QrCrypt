<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt;

/**
 * Class SerializationException
 * This is the base abstract class for all Exceptions that can be thrown inside of Mask\toString() in order for the
 * QrCrypt class to handle them gracefully.
 *
 * @package Tart\QrCrypt
 */
abstract class SerializationException extends \Exception {

} 