<?php

namespace App\Service;

use App\Entity\Server;
use App\Exception\EmptyNameException;
use App\Exception\WrongIpException;
use App\Exception\WrongMacException;
use App\Factory\ServerFactory;
use App\Hydrator\ServerHydrator;
use App\Provider\ServerProvider;
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
    * @param ServerFactory $factory
    * @param ServerHydrator $hydrator
    * @param EntityManagerInterface $manager
    * @param ServerProvider $provider
     */
    public function __construct(
        ServerFactory $factory,
        ServerHydrator $hydrator,
        EntityManagerInterface $manager,
        ServerProvider $provider
    )
    {
        $this->factory = $factory;
        $this->hydrator = $hydrator;
        $this->manager = $manager;
        $this->provider = $provider;
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
     * @param  Request $request
     *
     * @throws EmptyNameException
     * @throws WrongIpException
     * @throws WrongMacException
     */
    protected function validate(Request $request)
    {
        $name = $request->request->get('name');
        $ip = $request->request->get('ip');
        $mac = $request->request->get('mac');
        if (empty($name))
            throw new EmptyNameException;
        if (!\preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $ip))
            throw new WrongIpException;
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
