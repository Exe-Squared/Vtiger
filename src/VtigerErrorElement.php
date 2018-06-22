<?php

namespace Clystnet\Vtiger;

class VtigerErrorElement
{

    /**
     * @var string
     */
    private $_message;

    /**
     * @var int
     */
    private $_errorCode;

    /**
     * VtigerErrorElement constructor.
     *
     * @param string $message
     * @param string $errorCode
     */
    public function __construct($message, $errorCode)
    {

        $this->_message = $message;
        $this->_errorCode = $errorCode;

    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

}