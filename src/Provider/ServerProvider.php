<?php

namespace App\Provider;

use App\Entity\Server;
use App\Repository\ServerRepository;
use App\Exception\ServerNotFoundException;

class ServerProvider
{
    /**
     * @var ServerRepository
     */
    private $repository;

    /**
     * @param ServerRepository $repository
     */
    public function __construct(ServerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $id
     *
     * @return Server
     *
     * @throws \Exception
     */
    public function find(string $id): Server
    {
        $server = $this->repository->find($id);
        if (null === $server) {
            throw new ServerNotFoundException;
        }

        return $server;
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param array $criteria
     *
     * @return Server
     *
     * @throws \Exception
     */
    public function findOneBy(array $criteria): Server
    {
        $server = $this->repository->findOneBy($criteria);
        if (null === $server) {
            throw new ServerNotFoundException;
        }

        return $server;
    }

    /**
     * @param array $criteria
     *
     * @return array
     *
     * @throws \Exception
     */
    public function findBy(array $criteria): array
    {
        $servers = $this->repository->findBy($criteria);

        return $servers;
    }
}
