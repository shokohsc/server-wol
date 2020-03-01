<?php

namespace App\Service;

use App\Factory\DockerFactory;
use Docker\Docker;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\ContainersCreatePostResponse201;
use Docker\API\Model\DeviceMapping;
use Docker\API\Model\HostConfig;
use Docker\Stream\DockerRawStream;

class DockerService
{
    const WAKE_ON_LAN_IMAGE = 'jazzdd/wol';
    const WAKE_ON_LAN_TAG = 'latest';

    const PARSEC_IMAGE = 'jfyne/parsec';
    const PARSEC_TAG = 'latest';

    const ARP_IMAGE = 'alpine';
    const ARP_TAG = 'latest';

    /**
     * @var DockerFactory
     */
    private $factory;

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @param DockerFactory $factory
     */
    public function __construct(DockerFactory $factory)
    {
        $this->factory = $factory;
        $this->docker = $this->factory->build();
    }

    /**
     * @param  string $mac
     *
     * @throws Exception
     */
    public function wake(string $mac): void
    {
        try {
            $config = $this->configWakeOnLan($mac);
            $this->pull(self::WAKE_ON_LAN_IMAGE, self::WAKE_ON_LAN_TAG);
            $container = $this->create($config);
            $this->start($container);
            $this->wait($container);
            $this->remove($container);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Cannot use docker: %s", $e->getMessage()));
        }
    }

    /**
     * @param  string $mac
     *
     * @return ContainersCreatePostBody
     */
    protected function configWakeOnLan(string $mac): ContainersCreatePostBody
    {
        $config = new ContainersCreatePostBody();
        $config->setImage(self::WAKE_ON_LAN_IMAGE);
        $config->setEnv([
            'mac=' . $mac,
        ]);
        $host = new HostConfig();
        $host->setNetworkMode('host');
        $config->setHostConfig($host);

        return $config;
    }

    /**
     * @param  string $mac
     *
     * @throws Exception
     */
    public function sleep(string $mac): void
    {
        try {
            $config = $this->configSleepOnLan($mac);
            $this->pull(self::WAKE_ON_LAN_IMAGE, self::WAKE_ON_LAN_TAG);
            $container = $this->create($config);
            $this->start($container);
            $this->wait($container);
            $this->remove($container);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Cannot use docker: %s", $e->getMessage()));
        }
    }

    /**
     * @param  string $mac
     *
     * @return ContainersCreatePostBody
     */
    protected function configSleepOnLan(string $mac): ContainersCreatePostBody
    {
        // https://github.com/SR-G/sleep-on-lan
        $reversedMAC = implode(':', array_reverse(explode(':', $mac)));
        $config = new ContainersCreatePostBody();
        $config->setImage(self::WAKE_ON_LAN_IMAGE);
        $config->setEnv([
            'mac=' . $reversedMAC,
        ]);
        $host = new HostConfig();
        $host->setNetworkMode('host');
        $config->setHostConfig($host);

        return $config;
    }

    /**
     * @throws Exception
     */
    public function parsec(): void
    {
        try {
            $flag = false;
            $containers = $this->docker->containerList([
                'all' => true,
            ]);
            foreach ($containers as $container) {
                if ('/parsec' === $container->getNames()[0]) {
                    $this->remove($container);
                    $flag = true;
                }
            }

            if ($flag) { return; }
            $config = $this->configParsec();
            $this->pull(self::PARSEC_IMAGE, self::PARSEC_TAG);
            $container = $this->create($config, ['name' => 'parsec']);
            $this->start($container);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Cannot use docker: %s", $e->getMessage()));
        }
    }

    /**
     * @return ContainersCreatePostBody
     */
    protected function configParsec(): ContainersCreatePostBody
    {
        $parsecOptions = [
            'peer_id=' . \getenv('PEER_ID'),
            'encoder_bitrate=50',
            'network_try_lan=1',
            'client_vsync=1',
            'client_fullscreen=0',
            'client_windowed=0',
            'client_window_x=1920',
            'client_window_y=1080',
            'client_overlay=0',
            'decoder_software=0',
            'client_audio_buffer=100000',
            'server_admin_mute=0',
            'app_daemon=0',
        ];

        $devices = (new DeviceMapping())
            ->setPathOnHost('/dev/dri')
            ->setPathInContainer('/dev/dri')
            ->setCgroupPermissions('rwm')
        ;

        $host = (new HostConfig())
            ->setBinds([
                '/tmp/.X11-unix:/tmp/.X11-unix:ro',
                '/run/user/'.\getenv('PUID').'/pulse:/run/pulse:ro',
                'parsec_data:/home/parsec',
            ])
            ->setDevices([
                $devices,
            ])
        ;

        $config = (new ContainersCreatePostBody())
            ->setImage(self::PARSEC_IMAGE)
            ->setEnv([
                'DISPLAY=unix:0',
                'USER_UID='.\getenv('PUID'),
                'USER_GID='.\getenv('PGID'),
            ])
            ->setCmd([
                '/usr/bin/parsecd',
                implode(':', $parsecOptions),
            ])
            ->setHostConfig($host)
        ;

        return $config;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function arp(): array
    {
        $logs = [];
        try {
            $config = $this->configArp();
            $this->pull(self::ARP_IMAGE, self::ARP_TAG);
            $container = $this->create($config);
            $stream = $this->attach($container);
            $this->start($container);

            $stream->onStdout(function($stdout) use (&$logs){
                foreach (explode("\n", $stdout) as $output) {
                    $logs[] = $output;
                }
            });

            $stream->wait();
            $this->wait($container);
            $this->remove($container);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Cannot use docker: %s", $e->getMessage()));
        }

        return $logs;
    }

    /**
     * @return ContainersCreatePostBody
     */
    protected function configArp(): ContainersCreatePostBody
    {
        $host = (new HostConfig())
            ->setNetworkMode('host')
        ;

        $config = (new ContainersCreatePostBody())
            ->setImage(self::ARP_IMAGE)
            ->setHostConfig($host)
            ->setAttachStdout(true)
            ->setCmd([
                'arp',
                '-a',
            ])
        ;

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
     * @param  array                    $queryParameters
     *
     * @return ContainersCreatePostResponse201
     */
    protected function create(ContainersCreatePostBody $config, array $queryParameters = []): ContainersCreatePostResponse201
    {
        return $this->docker->containerCreate($config, $queryParameters);
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function start(ContainersCreatePostResponse201 $container): void
    {
        $this->docker->containerStart($container->getId());
    }

    /**
     * @param  $container
     */
    protected function stop($container): void
    {
        $this->docker->containerStop($container->getId());
    }

    /**
     * @param  ContainersCreatePostResponse201 $container
     */
    protected function wait(ContainersCreatePostResponse201 $container): void
    {
        $this->docker->containerWait($container->getId());
    }

    /**
     * @param  $container
     */
    protected function remove($container): void
    {
        $this->docker->containerDelete($container->getId(), [
            'v' => true,
            'force' => true,
        ]);
    }

    /**
     * @param  $container
     *
     * @return DockerRawStream
     */
    protected function attach($container): DockerRawStream
    {
        return $this->docker->containerAttach($container->getId(), [
            'stream' => true,
            'stdout' => true,
            'logs' => true,
        ]);
    }
}
