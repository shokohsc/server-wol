<?php

namespace App\Service;

use App\Hydrator\ServerHydrator;
use App\Provider\ServerProvider;
use App\Service\ServerService;
use App\Status\ServerStatus;
use Doctrine\ORM\EntityManagerInterface;
use JJG\Ping;
use Diegonz\PHPWakeOnLan\PHPWakeOnLan;

class WolService
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
    }

    /**
     * @param  string $id
     *
     * @return array
     *
     * @throws \Exception
     */
    public function ping(string $id): array
    {
        $server = $this->service->read($id);
        $ping = new Ping($server['ip']);
        $ping->setTtl(2);
        $ping->setTimeout(1);
        $latency = $ping->ping('fsockopen');

        if ($latency !== false) {
            $server['status'] = ServerStatus::STATUS_AWAKE;
            $entity = $this->provider->find($server['id']);
            $entity = $this->hydrator->hydrate($entity, $server);
            $this->manager->flush();

            return $server;
        }
        $server['status'] = ServerStatus::STATUS_ASLEEP;
        $entity = $this->provider->find($server['id']);
        $entity = $this->hydrator->hydrate($entity, $server);
        $this->manager->flush();

        return $server;
    }

    /**
     * @param  string $id
     *
     * @return array
     */
    public function wake(string $id): array
    {
        $server = $this->service->read($id);
        if ($server['status'] === ServerStatus::STATUS_AWAKE) {
            return $server;
        }

        $wol = new PHPWakeOnLan('192.168.1.255', 9);
        $wol->wake([$server['mac']]);

        $server['status'] = ServerStatus::STATUS_AWAKE;
        $entity = $this->provider->find($server['id']);
        $entity = $this->hydrator->hydrate($entity, $server);
        $this->manager->flush();

        return $server;
    }
}
