<?php

namespace App\Controller;

use App\Exception\ServerSleepingException;
use App\Service\DockerService;
use App\Service\ServerService;
use App\Status\ServerStatus;
use App\Service\PingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
  * @Route("/api")
  */
class ApiController extends AbstractController
{
    /**
      * @Route("/server", methods={"GET"})
      */
    public function listServer(ServerService $service): JsonResponse
    {
        return new JsonResponse($service->list());
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"GET"})
      */
    public function readServer(string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->read($id));
    }

    /**
      * @Route("/server", methods={"POST"})
      */
    public function createServer(Request $request, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->create($request));
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"POST"})
      */
    public function updateServer(Request $request, string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->update($request, $id));
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"DELETE"})
      */
    public function deleteServer(string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->delete($id));
    }

    /**
      * @Route("/ping/{id}", requirements={"id"="\d+"}, methods={"GET"})
      * @Cache(expires="+10 seconds", public=true)
      */
    public function ping(string $id, PingService $service, ServerService $serverService): JsonResponse
    {
        $server = $serverService->read($id);
        $latency = $service->ping($server['ip']);
        $status = $latency !== false ? ServerStatus::STATUS_AWAKE : ServerStatus::STATUS_ASLEEP;
        $server = $serverService->updateStatus($id, $status);

        return new JsonResponse($server);
    }

    /**
      * @Route("/wake/{id}", requirements={"id"="\d+"}, methods={"GET"})
      * @Cache(expires="+10 seconds", public=true)
      */
    public function wake(string $id, DockerService $dockerService, ServerService $serverService): JsonResponse
    {
        $server = $serverService->read($id);
        if (ServerStatus::STATUS_AWAKE === $server['status']) {
            return new JsonResponse($server);
        }

        return new JsonResponse($dockerService->wake($server['mac']));
    }

    /**
      * @Route("/sleep/{id}", requirements={"id"="\d+"}, methods={"GET"})
      * @Cache(expires="+10 seconds", public=true)
      */
    public function sleep(string $id, DockerService $dockerService, ServerService $serverService): JsonResponse
    {
        $server = $serverService->read($id);
        if (ServerStatus::STATUS_ASLEEP === $server['status']) {
            return new JsonResponse($server);
        }

        return new JsonResponse($dockerService->sleep($server['mac']));
    }

    /**
      * @Route("/parsec/{id}", requirements={"id"="\d+"}, methods={"GET"})
      * @Cache(expires="+10 seconds", public=true)
      */
    public function parsec(string $id, DockerService $dockerService, ServerService $serverService): JsonResponse
    {
        $server = $serverService->read($id);
        if (ServerStatus::STATUS_ASLEEP === $server['status']) {
            throw new ServerSleepingException;
        }

        return new JsonResponse($dockerService->parsec());
    }
}
