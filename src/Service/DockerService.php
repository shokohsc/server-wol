<?php

namespace App\Service;

use App\Hydrator\ServerHydrator;
use App\Provider\ServerProvider;
use App\Service\ServerService;
use App\Status\ServerStatus;
use Docker\Docker;
use Docker\DockerClientFactory;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\ContainersCreatePostResponse201;
use Docker\API\Model\HostConfig;
use Doctrine\ORM\EntityManagerInterface;

class DockerService
{
    /**
     * @var ServerService
     */
    private $service;

    /**
     * @var ServerProvider
     */
    private $provider;

    /**
     * @var ServerHydrator
     */
    private $hydrator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @param ServerService $service
     * @param ServerProvider $provider
     * @param ServerHydrator $hydrator
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        ServerService $service,
        ServerProvider $provider,
        ServerHydrator $hydrator,
        EntityManagerInterface $manager
    )
    {
        $this->service = $service;
        $this->provider = $provider;
        $this->hydrator = $hydrator;
        $this->manager = $manager;
        $client = DockerClientFactory::create();
        $this->docker = Docker::create($client);
    }

    /**
     * @param  string $id
     *
     * @return array
     *
     * @throws Exception
     */
    public function wake(string $id): array
    {
        $server = $this->service->read($id);
        if ($server['status'] === ServerStatus::STATUS_AWAKE) {
            return $server;
        }

        try {
            $config = $this->config($server);
            $container = $this->create($config);
            $this->start($container);
            $this->wait($container);
            $this->remove($container);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Cannot use docker: %s", $e->getMessage()));
        }


        $server['status'] = ServerStatus::STATUS_AWAKE;
        $entity = $this->provider->find($server['id']);
        $entity = $this->hydrator->hydrate($entity, $server);
        $this->manager->flush();

        return $server;
    }

    /**
     * @param  array                    $server
     *
     * @return ContainersCreatePostBody
     */
    protected function config(array $server): ContainersCreatePostBody
    {
        $config = new ContainersCreatePostBody();
        $config->setImage('jazzdd/wol:latest');
        $config->setEnv([
            'mac=' . $server['mac'],
        ]);
        $host = new HostConfig();
        $host->setNetworkMode('host');
        $config->setHostConfig($host);

        return $config;
    }

    /**
     * @param  ContainersCreatePostBody $config
     *
     * @return ContainersCreatePostResponse201
     */
    protected function create(ContainersCreatePostBody $config): ContainersCreatePostResponse201
    {
        return $this->docker->containerCreate($config);
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function start(ContainersCreatePostResponse201 $container)
    {
        $this->docker->containerStart($container->getId());
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function wait(ContainersCreatePostResponse201 $container)
    {
        $this->docker->containerWait($container->getId());
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function remove(ContainersCreatePostResponse201 $container)
    {
        $this->docker->containerDelete($container->getId());
    }
}
