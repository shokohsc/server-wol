<?php

namespace App\Service;

use App\Entity\Server;
use App\Exception\EmptyNameException;
use App\Exception\WrongIpException;
use App\Exception\WrongMacException;
use App\Factory\ServerFactory;
use App\Hydrator\ServerHydrator;
use App\Provider\ServerProvider;
use App\Service\NetworkService;
use App\Status\ServerStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class ServerService
{
    /**
     * @var ServerFactory
     */
    private $factory;

    /**
     * @var ServerHydrator
     */
    private $hydrator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ServerProvider
     */
    private $provider;

    /**
     * @var NetworkService
     */
    private $service;

    /**
    * @param ServerFactory $factory
    * @param ServerHydrator $hydrator
    * @param EntityManagerInterface $manager
    * @param ServerProvider $provider
    * @param NetworkService $service
     */
    public function __construct(
        ServerFactory $factory,
        ServerHydrator $hydrator,
        EntityManagerInterface $manager,
        ServerProvider $provider,
        NetworkService $service
    )
    {
        $this->factory = $factory;
        $this->hydrator = $hydrator;
        $this->manager = $manager;
        $this->provider = $provider;
        $this->service = $service;
    }

    /**
     * @return array
     */
    public function list(): array
    {
        $servers = $this->provider->findAll();
        $tmp = [];
        foreach ($servers as $server) {
            $tmp[] = $this->serialize($server);
        }

        return $tmp;
    }

    /**
     * @param  string $id
     *
     * @return array
     *
     * @throws \Exception
     */
    public function read(string $id): array
    {
        return $this->serialize($this->provider->find($id));
    }

    /**
     * @param  Request $request
     *
     * @return array
     */
    public function create(Request $request): array
    {
        $this->validate($request);
        $server = $this->factory->build($request->request->all());

        $server->setName($this->service->getServerHostname($server->getMac()));
        $server->setIp($this->service->getServerIp($server->getMac()));

        $this->manager->persist($server);
        $this->manager->flush();

        return $this->serialize($server);
    }

    /**
     * @param  Request $request
     * @param  string $id
     *
     * @return array
     */
    public function update(Request $request, string $id): array
    {
        $server = $this->provider->find($id);
        $this->validate($request);

        $server->setName($this->service->getServerHostname($server->getMac()));
        $server->setIp($this->service->getServerIp($server->getMac()));

        $server = $this->hydrator->hydrate($server, $request->request->all());
        $this->manager->flush();

        return $this->serialize($server);
    }

    /**
     * @param  string $id
     *
     * @return array
     */
    public function delete(string $id): array
    {
        $server = $this->provider->find($id);
        $this->manager->remove($server);
        $this->manager->flush();

        return [];
    }

    /**
     * @param  string $id
     * @param  string $status
     *
     * @return array
     */
    public function updateStatus(string $id, string $status): array
    {
        $server = $this->provider->find($id);
        $server->setStatus($status);
        $this->manager->flush();

        return $this->serialize($server);
    }

    /**
     * @param  Request $request
     *
     * @throws EmptyNameException
     * @throws WrongIpException
     * @throws WrongMacException
     */
    protected function validate(Request $request)
    {
        $mac = $request->request->get('mac');
        if (!\preg_match('/(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})/', $mac))
            throw new WrongMacException;
    }

    /**
     * @param  Server $server
     *
     * @return array
     */
    protected function serialize(Server $server): array
    {
        return [
            'id' => $server->getId(),
            'name' => $server->getName(),
            'mac' => $server->getMac(),
            'ip' => $server->getIp(),
            'status' => $server->getStatus(),
        ];
    }
}
