<?php

namespace App\Service;

use App\Service\DockerService;

class NetworkService
{
    /**
     * @var DockerService
     */
    private $service;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param DockerService $service
     */
    public function __construct(DockerService $service)
    {
        $this->service = $service;
        $this->cache = [];
    }

    /**
     * @return $this
     */
    protected function getCache(): array
    {
        return $this->scan()->cache;
    }

    /**
     * @param array $cache
     *
     * @return NetworkService
     */
    protected function setCache(array $cache): NetworkService
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return void
     *
     * @return NetworkService
     */
    protected function scan(): NetworkService
    {
        if (empty($this->cache)) {
            $this->setCache($this->service->arp());
        }

        return $this;
    }

    /**
     * @return NetworkService
     */
    public function reset(): NetworkService
    {
        $this->setCache([]);

        return $this;
    }

    /**
     * @param string $mac
     *
     * @return string
     */
    public function getServerHostname(string $mac): string
    {
        $matches = [];
        foreach ($this->getCache() as $mapping) {
            preg_match('/^(?<hostname>.+)\ \((?<ip>((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.?){4})\)\ at\ ((?<mac>([a-f0-9][a-f0-9]:?){6})\ (.+)|<incomplete>\ )\ on\ (?<nic>.+)$/i', $mapping, $matches);
            if (isset($matches['mac']) && isset($matches['hostname']) && strtolower($mac) === strtolower($matches['mac'])) {
                return $matches['hostname'];
            }
        }

        return 'temp';
    }

    /**
     * @param string $mac
     *
     * @return string
     */
    public function getServerIp(string $mac): string
    {
        $matches = [];
        foreach ($this->getCache() as $mapping) {
            preg_match('/^(?<hostname>.+)\ \((?<ip>((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.?){4})\)\ at\ ((?<mac>([a-f0-9][a-f0-9]:?){6})\ (.+)|<incomplete>\ )\ on\ (?<nic>.+)$/i', $mapping, $matches);
            if (isset($matches['mac']) && isset($matches['ip']) && strtolower($mac) === strtolower($matches['mac'])) {
                return $matches['ip'];
            }
        }

        return 'temp';
    }
}
