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
        if (\getenv('DOCKER_TLS_VERIFY') && 'true' === \getenv('DOCKER_TLS_VERIFY')) {
            $stream_context = [
                'local_cert' => \getenv('DOCKER_CERT_PATH').DIRECTORY_SEPARATOR.'cert.pem',
                'local_pk' => \getenv('DOCKER_CERT_PATH').DIRECTORY_SEPARATOR.'key.pem',
            ];
            $client = DockerClientFactory::create([
                'remote_socket' => \getenv('DOCKER_HOST'),
                'ssl' => true,
                'stream_context_options' => $stream_context,
            ]);
        }

        return Docker::create($client);
    }
}
