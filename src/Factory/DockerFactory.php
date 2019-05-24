<?php

namespace App\Factory;

use Docker\Docker;
use Docker\DockerClientFactory;

class DockerFactory
{
    /**
     * @return Docker
     */
    public function build(): Docker
    {
        $client = DockerClientFactory::create();

        return Docker::create($client);
    }
}
