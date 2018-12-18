<?php

namespace App\Services;

/**
 * Class ServiceException
 *
 * Runtime exception which is generated on the service level. It signals about an error in business logic.
 *
 * @package App\Exceptions
 */
class ServiceException extends \RuntimeException
{
    public function __construct($e, $message = '', $code = 0, $logger = null) { 
        // $logger->critical(
        //                 $code. ' '. $message
        //         );
        return parent::__construct($e, $message, $code);
    }
}
