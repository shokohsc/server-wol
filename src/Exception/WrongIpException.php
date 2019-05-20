<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class WrongIpException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Incorrect Ip address.';

    /**
     * {@inheritdoc}
     */
    protected $code = Response::HTTP_BAD_REQUEST;

}
