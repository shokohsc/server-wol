<?php

namespace App\Hydrator;

use App\Entity\Server;

class ServerHydrator
{
    /**
     * @param Server $server
     * @param array $data
     *
     * @return Server
     */
    public function hydrate(Server $server, array $data): Server
    {
        return $server
            ->setMac(isset($data['mac']) ? $data['mac'] : $server->getMac())
            ->setStatus(isset($data['status']) ? $data['status'] : $server->getStatus())
        ;
    }
}
