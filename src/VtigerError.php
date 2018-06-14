<?php

namespace Clystnet\Vtiger;

class VtigerError extends \Exception {

    /** @var string */
    protected $vTigerErrorCode;

    /**
     * VtigerError constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param string         $vTigerErrorCode
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, $vTigerErrorCode = "", Throwable $previous = null) {

        parent::__construct($message, $code, $previous);

        $this->vTigerErrorCode = $vTigerErrorCode;

    }

    /**
     * @return string
     */
    public function getVTigerErrorCode() {
        return $this->vTigerErrorCode;
    }

}