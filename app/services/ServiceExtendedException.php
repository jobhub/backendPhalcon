<?php

namespace App\Services;

/**
 * Class ServiceException
 *
 * Runtime exception which is generated on the service level. It signals about an error in business logic.
 *
 * @package App\Exceptions
 */
class ServiceExtendedException extends ServiceException
{
    //Some added data
    private $data = null;
    public function __construct(string $message = '', int $code = 0,\Throwable $e = null, $logger = null, $data = null) {
        // $logger->critical(
        //                 $code. ' '. $message
        //         );
        $this->data = $data;
        return parent::__construct($message, $code,$e);
    }

    public function getData(){
        return $this->data;
    }
}
