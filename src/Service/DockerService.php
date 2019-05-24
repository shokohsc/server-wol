<?php

namespace App\Service;

use App\Factory\DockerFactory;
use App\Hydrator\ServerHydrator;
use App\Provider\ServerProvider;
use App\Service\ServerService;
use App\Status\ServerStatus;
use Docker\Docker;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\ContainersCreatePostResponse201;
use Docker\API\Model\HostConfig;
use Doctrine\ORM\EntityManagerInterface;

class DockerService
{
    const WAKE_ON_LAN_IMAGE = 'jazzdd/wol';
    const WAKE_ON_LAN_TAG = 'latest';

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
     * @var DockerFactory
     */
    private $factory;

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @param ServerService $service
     * @param ServerProvider $provider
     * @param ServerHydrator $hydrator
     * @param EntityManagerInterface $manager
     * @param DockerFactory $factory
     */
    public function __construct(
        ServerService $service,
        ServerProvider $provider,
        ServerHydrator $hydrator,
        EntityManagerInterface $manager,
        DockerFactory $factory
    )
    {
        $this->service = $service;
        $this->provider = $provider;
        $this->hydrator = $hydrator;
        $this->manager = $manager;
        $this->factory = $factory;
        $this->docker = $this->factory->build();
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
            $this->pull(self::WAKE_ON_LAN_IMAGE, self::WAKE_ON_LAN_TAG);
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
        $config->setImage(self::WAKE_ON_LAN_IMAGE);
        $config->setEnv([
            'mac=' . $server['mac'],
        ]);
        $host = new HostConfig();
        $host->setNetworkMode('host');
        $config->setHostConfig($host);

        return $config;
    }

    /**
     * @param string $image
     * @param string $tag
     */
    protected function pull(string $image, string $tag): void
    {
        $image = $this->docker->imageCreate('', [
            'fromImage' => $image,
            'tag' => $tag
        ]);

        $image->wait();
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
    protected function start(ContainersCreatePostResponse201 $container): void
    {
        $this->docker->containerStart($container->getId());
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function wait(ContainersCreatePostResponse201 $container): void
    {
        $this->docker->containerWait($container->getId());
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function remove(ContainersCreatePostResponse201 $container): void
    {
        $this->docker->containerDelete($container->getId());
    }
}
