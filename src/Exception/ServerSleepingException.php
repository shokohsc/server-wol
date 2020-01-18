<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ServerSleepingException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Server is sleeping.';

    /**
     * {@inheritdoc}
     */
    protected $code = Response::HTTP_BAD_REQUEST;

}
