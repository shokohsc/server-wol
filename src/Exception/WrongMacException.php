<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class WrongMacException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Incorrect Mac address.';

    /**
     * {@inheritdoc}
     */
    protected $code = Response::HTTP_BAD_REQUEST;

}
