<?php

namespace Clystnet\Vtiger;

class VtigerError extends \Exception
{

    /**
     * Build a new VtigerError using the specified error from the errors array
     *
     * @param VtigerErrorElement[] $errorsArray
     * @param int $codeToUse
     * @param string|null $extraMessage
     *
     * @return VtigerError
     */
    public static function init($errorsArray, $codeToUse, $extraMessage = null)
    {
        return new self($errorsArray[$codeToUse]->getMessage() . $extraMessage,
            $errorsArray[$codeToUse]->getErrorCode());
    }

}