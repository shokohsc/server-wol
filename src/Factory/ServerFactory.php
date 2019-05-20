<?php

namespace App\Factory;

use App\Entity\Server;
use App\Status\ServerStatus;

class ServerFactory
{
    /**
     * @param array $data
     *
     * @return Server
     */
    public function build(array $data): Server
    {
        return (new Server)
            ->setName($data['name'])
            ->setIp($data['ip'])
            ->setMac($data['mac'])
            ->setStatus(ServerStatus::STATUS_ASLEEP)
        ;
    }
}
