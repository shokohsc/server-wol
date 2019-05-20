<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class EmptyNameException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Empty name.';

    /**
     * {@inheritdoc}
     */
    protected $code = Response::HTTP_BAD_REQUEST;

}
