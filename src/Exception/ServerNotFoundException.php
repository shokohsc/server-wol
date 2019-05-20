<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ServerNotFoundException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Server not found.';

    /**
     * {@inheritdoc}
     */
    protected $code = Response::HTTP_NOT_FOUND;

}
