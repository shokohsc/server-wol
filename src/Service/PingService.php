<?php

namespace App\Service;

use JJG\Ping;

class PingService
{
    /**
     * @var Ping
     */
    private $ping;

    /**
     * @param Ping $ping
     */
    public function __construct(Ping $ping)
    {
        $this->ping = $ping;
    }

    /**
     * @param  string $ip
     *
     * @return boolean|float
     *
     * @throws \Exception
     */
    public function ping(string $ip)
    {
        $this->ping->setHost($ip);
        $this->ping->setTtl(50);
        $this->ping->setTimeout(3);

        return $this->ping->ping();
    }
}
