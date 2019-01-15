<?php

namespace App\Services;

/**
 * Class ServiceExtendedException
 *
 * Runtime exception which is generated on the service level.
 *
 * @package App\Exceptions
 */
class ServiceExtendedException extends ServiceException
{
    //Some added data
    private $data = null;

    public function __construct(string $message = '', int $code = 0,\Throwable $e = null, $logger = null, $data = null) {
        $this->data = $data;
        return parent::__construct($message, $code,$e);
    }

    public function getData(){
        return $this->data;
    }
}
