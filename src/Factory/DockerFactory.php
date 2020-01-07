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
            $options = [
                'allow_self_signed' => true,
                'cafile' => \getenv('DOCKER_CERT_PATH').DIRECTORY_SEPARATOR.'ca.pem',
                'local_cert' => \getenv('DOCKER_CERT_PATH').DIRECTORY_SEPARATOR.'cert.pem',
                'local_pk' => \getenv('DOCKER_CERT_PATH').DIRECTORY_SEPARATOR.'key.pem',
            ];
            $client = DockerClientFactory::create([
                'remote_socket' => \getenv('DOCKER_HOST'),
                'ssl' => true,
                'stream_context_options' => [
                    'ssl' => $options,
                ],
            ]);
        }

        return Docker::create($client);
    }
}
