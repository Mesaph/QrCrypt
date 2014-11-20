<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt;

use Endroid\QrCode\QrCode;
use gnupg;
use Tart\QrCrypt\Exceptions\FileSystemException;
use Tart\QrCrypt\Exceptions\MissingInformationException;

/**
 * Class QrCrypt
 * @package Tart\QrCrypt
 */
class QrCrypt {

    /**
     * Whether the encoded data should be encrypted.
     * @var bool
     */
    private $encrypted = false;

    /**
     * Whether the encoded data should be signed.
     * @var bool
     */
    private $signed = false;

    /**
     * The directory where the qr-code should be stored.
     * @var string
     */
    private $directory;

    /**
     * An object that implements the Mask interface which stores the data that should be encoded.
     * @var Mask
     */
    private $mask;

    /**
     * The maximum qr-code version that should be used.
     * @var int
     */
    private $maxVersion;

    /**
     * The minimal error correction level that should be used.
     * @var string
     */
    private $minErrorCorrection;

    /**
     * This array maps the error correction levels of the Endroid\QrCode\QrCode class to the commonly used characters.
     * @var array
     */
    private $errorCorrectionMap = array(
        QrCode::LEVEL_HIGH      => 'H',
        QrCode::LEVEL_LOW       => 'L',
        QrCode::LEVEL_MEDIUM    => 'M',
        QrCode::LEVEL_QUARTILE  => 'Q'
    );

    /**
     * This string will be prepended to all qr-codes
     * @var string
     */
    private $magic = 'QCR';

    /**
     * This is the GnuPG instance
     * @var gnupg
     */
    private $gpg;

    /**
     * This is the QrCode instance
     * $var QrCode
     */
    private $qrCode;

    /**
     * Constructor function
     * @param Mask $mask An object that implements the Mask interface which stores the data that should be encoded.
     */
    public function __construct(Mask $mask) {
        $this->mask = $mask;

        $this->gpg = new gnupg();
        $this->gpg->setsignmode(GNUPG_SIG_MODE_CLEAR);

        $this->qrCode = new QrCode();
    }

    /**
     * This method generates the actual qr-code and saves it into an image file.
     * @param string $filetype This should be one of 'png', 'gif', 'jpeg' and 'wbmp'
     * @param string|null $filename If specified, the image will be saved into this file. If not, a filename will be
     * automatically chosen. The filename should be relative to the set directory.
     * @return string The filename of the generated image relative to the set directory.
     */
    public function save($filetype = 'png', $filename = null) {
        if(is_null($this->directory)) {
            throw new MissingInformationException('directory is not set');
        }
        if(!is_dir($this->directory)) {
            throw new FileSystemException('directory does not exist');
        }
        if(!in_array($filetype, ['png', 'gif', 'jpeg', 'wbmp'])) {
            throw new \InvalidArgumentException('unknown filetype');
        }

        $image = $this->display($filetype);

        if(is_null($filename)) {
            $filename = md5($image) . '.' . $filetype;

            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            while (file_exists($this->directory . DIRECTORY_SEPARATOR . $filename)) {
                $filename = $characters[mt_rand(0, strlen($characters) - 1)] . $filename;
            }
        } else {
            if(file_exists($this->directory . DIRECTORY_SEPARATOR . $filename)) {
                throw new FileSystemException('file already exists');
            }
        }

        file_put_contents($this->directory . DIRECTORY_SEPARATOR . $filename, $image);

        return $filename;
    }

    /**
     * This method generates the actual qr-code and returns the raw image data.
     * @param string $filetype
     * @return string
     */
    public function display($filetype = 'png') {
        $this->qrCode->setText($this->encode());
        $this->qrCode->setVersion($this->maxVersion); //TODO: determine whether a smaller version would suffice
        $this->qrCode->setErrorCorrection($this->minErrorCorrection); //TODO: determine whether a better error correction would be possible

        return $this->qrCode->get($filetype);
    }

    /**
     * Creates the string that will be written into the qr-code.
     * This method signs and/or encrypts the data provided by the mask and combines it with the magic string and the
     * id of the mask
     * @return string The encoded data
     */
    private function encode() {
        $maskString = $this->mask->toString();
        if($this->encrypted && $this->signed) {
            $mode = 's';
            $maskString = $this->stripgpg($this->gpg->encryptsign($maskString));
        } elseif($this->encrypted) {
            $mode = 's';
            $maskString = $this->stripgpg($this->gpg->encrypt($maskString));
        } elseif($this->signed) {
            $mode = 's';
            $maskString = $this->stripgpg($this->gpg->sign($maskString));
        } else {
            $mode = 'n';
        }

        return $this->magic . ':' . $this->mask->getId() . ' :' . $mode . ':' . $maskString;
    }

    /**
     * Strips the GPG header and footer from a string to save space
     * @param string $string
     * @return string
     */
    private function stripgpg($string) {
        $header = "-----BEGIN PGP MESSAGE-----\nVersion: GnuPG v1\n\n";
        $footer = "\n-----END PGP MESSAGE-----";
        return preg_replace('#^' . $header . '(.*)' . $footer . '$#s', '$1', $string);
    }

    /**
     * @return boolean
     */
    public function isEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * @param boolean $encrypted
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;
    }

    /**
     * @return boolean
     */
    public function isSigned()
    {
        return $this->signed;
    }

    /**
     * @param boolean $signed
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return int
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }

    /**
     * @param $maxVersion
     */
    public function setMaxVersion($maxVersion)
    {
        $maxVersion = (int) $maxVersion;

        if($maxVersion != -1 && $maxVersion < 1 && $maxVersion > 40)
            throw new \InvalidArgumentException();

        $this->maxVersion = $maxVersion;
    }

    /**
     * @return string
     */
    public function getMinErrorCorrection()
    {
        return $this->errorCorrectionMap[$this->minErrorCorrection];
    }

    /**
     * Set the minimum error correction level.
     * This sets the minimum error correction level that should be used.
     * @param int|string $minErrorCorrection
     */
    public function setMinErrorCorrection($minErrorCorrection)
    {
        if(is_string($minErrorCorrection)) {
            $key = array_search($minErrorCorrection, $this->errorCorrectionMap);

            if($key !== false) {
                $this->minErrorCorrection = $key;
                return;
            }
        }

        if(is_int($minErrorCorrection) && array_key_exists($minErrorCorrection, $this->errorCorrectionMap)) {
            $this->errorCorrectionMap = $minErrorCorrection;
            return;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Add a new key for encryption
     * @param string $fingerprint
     */
    public function addEncryptKey($fingerprint) {
        $this->gpg->addencryptkey($fingerprint);
    }

    /**
     * Add a new key for signing
     * @param string $fingerprint
     */
    public function addSignKey($fingerprint) {
        $this->gpg->addsignkey($fingerprint);
    }

    /**
     * @param int $size
     */
    public function setSize($size) {
        $this->qrCode->setSize($size);
    }

    /**
     * @param int $padding
     */
    public function setPadding($padding) {
        $this->qrCode->setPadding($padding);
    }

}