<?php

/**
 * @author Thomas Maqua <maqua@cs.uni-bonn.de>
 */

namespace Tart\QrCrypt\Masks;
use Tart\QrCrypt\Mask;
use Tart\QrCrypt\Exceptions\MissingInformationException;


class TransactionMask implements Mask {

    const SEPARATOR = "\t";

    /**
     * The IBAN of the account of the Originator
     * @var string
     */
    private $at01 = null;

    /**
     * The name of the Originator
     * @var string
     */
    private $at02 = null;

    /**
     * The amount of the credit transfer in euro cents (!)
     * @var int
     */
    private $at04 = null;

    /**
     * The Remittance Information sent by the Originator to the
     * Beneficiary in the Credit Transfer Instruction
     * @var string
     */
    private $at05 = null;

    /**
     * The Requested Execution Date of the instruction
     * @var \Datetime
     */
    private $at07 = null;

    /**
     * The IBAN of the account of the Beneficiary
     * @var string
     */
    private $at20 = null;

    /**
     * The name of the Beneficiary
     * @var string
     */
    private $at21 = null;

    /**
     * The Settlement Date of the credit transfer
     * @var \Datetime
     */
    private $at42 = null;

    /**
     * The purpose of the credit transfer
     * @var string
     */
    private $at44 = null;

    /**
     * Parses the available Information into a string that can be encoded into a Qr-Code
     * @return string
     * @throws MissingInformationException
     */
    public function toString()
    {
        $required = [   $this->at01,
                        $this->at02,
                        $this->at04,
                        $this->at05,
                        $this->at07,
                        $this->at20,
                        $this->at21,
                        $this->at44];
        foreach($required as $value) {
            if(is_null($value)) {
                throw new MissingInformationException();
            }
        }

        $values = [];

        array_push($values, $this->at01);
        array_push($values, $this->at02);
        array_push($values, pack('P', $this->at04));
        array_push($values, $this->at05);
        array_push($values, pack('V', $this->at07->getTimestamp()));
        array_push($values, $this->at20);
        array_push($values, $this->at21);

        if(!is_null($this->at42)) {
            array_push($values, pack('V', $this->at42->getTimestamp()));
        } else {
            array_push($values, '');
        }

        array_push($values, $this->at44);

        return implode(self::SEPARATOR, $values);

    }

    /**
     * Returns the Mask identifier string
     * @return string
     */
    public function getId()
    {
        return 'trans';
    }

    /**
     * Creates a new instance of the transaction mask.
     * @param array $options An associative array containing the attributes of the transaction
     */
    public function __construct($options) {
        foreach($options as $key => $value) {
            switch($key) {
                case 'at01':
                case 'originator_iban':

                    if(!$this->isValidIban($value)) {
                        throw new \InvalidArgumentException($value . ' is not a valid IBAN.');
                    }

                    $this->at01 = $value;
                    break;

                case 'at02':
                case 'originator_name':

                    $this->at02 = $value;
                    break;

                case 'at04':
                case 'amount':

                    if(!$this->isValidAmount($value)) {
                        throw new \InvalidArgumentException($value . ' is not a valid amount. Please note that the '.
                            'amout should be specified in euro cents.');
                    }

                    $this->at04 = $value;
                    break;

                case 'at05':
                case 'remittance_information':

                    $this->at05 = $value;
                    break;

                case 'at07':
                case 'time_execution':

                    if(!($value instanceof \DateTime)) {
                        throw new \InvalidArgumentException($value . ' should be of type \DateTime, but is not.');
                    }

                    $this->at07 = $value;
                    break;

                case 'at20':
                case 'beneficiary_iban':

                    if(!$this->isValidIban($value)) {
                        throw new \InvalidArgumentException($value . ' is not a valid IBAN.');
                    }

                    $this->at20 = $value;
                    break;

                case 'at21':
                case 'beneficiary_name':

                    $this->at21 = $value;
                    break;

                case 'at42':
                case 'time_settlement':

                    if(!($value instanceof \DateTime)) {
                        throw new \InvalidArgumentException($value . ' should be of type \DateTime, but is not.');
                    }

                    $this->at42 = $value;
                    break;

                case 'at44':
                case 'purpose':

                    if(!$this->isValidPurpose($value)) {
                        throw new \InvalidArgumentException($value . ' is not a valid purpose.');
                    }

                    $this->at44 = $value;
                    break;

                default:
                    throw new \InvalidArgumentException('Could not interpret options key [\'' . $key . '\'].');

            }
        }
    }

    /**
     * Tests whether the specified IBAN is valid
     * @param string $iban
     * @return bool
     */
    public function isValidIban($iban) {
        if(!is_string($iban)) return false;

        if(!preg_match('^[0-9a-zA-Z]{4,34}$', $iban)) return false;

        return true;
    }

    /**
     * Tests whether the specified amount is valid
     * @param $amount
     * @return bool
     */
    public function isValidAmount($amount) {

        if(!is_int($amount)) return false;

        if($amount <= 0) return false;

        if($amount > 99999999999) return false;

        return true;
    }

    /**
     * Tests whether the specified purpose is valid
     * @param $purpose
     * @return bool
     */
    public function isValidPurpose($purpose) {

        if(!is_string($purpose)) return false;

        if(strlen($purpose) > 4) return false;

        return true;
    }

} 