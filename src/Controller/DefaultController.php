<?php

namespace App\Controller;

use App\Service\DockerService;
use App\Service\ServerService;
use App\Service\WolService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
      * @Route("/", methods={"GET"}))
      * @Cache(expires="+5 minutes", public=true)
      */
    public function index()
    {
        return $this->render('base.html.twig');
    }

    /**
      * @Route("/server", methods={"GET"}))
      */
    public function listServer(ServerService $service): JsonResponse
    {
        return new JsonResponse($service->list());
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"GET"}))
      */
    public function readServer(string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->read($id));
    }

    /**
      * @Route("/server", methods={"POST"}))
      */
    public function createServer(Request $request, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->create($request));
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"POST"}))
      */
    public function updateServer(Request $request, string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->update($request, $id));
    }

    /**
      * @Route("/server/{id}", requirements={"id"="\d+"}, methods={"DELETE"}))
      */
    public function deleteServer(string $id, ServerService $service): JsonResponse
    {
        return new JsonResponse($service->delete($id));
    }

    /**
      * @Route("/ping/{id}", requirements={"id"="\d+"}, methods={"GET"}))
      * @Cache(expires="+10 seconds", public=true)
      */
    public function ping(string $id, WolService $service): JsonResponse
    {
        return new JsonResponse($service->ping($id));
    }

    /**
      * @Route("/wake/{id}", requirements={"id"="\d+"}, methods={"GET"}))
      * @Cache(expires="+10 seconds", public=true)
      */
    public function wake(string $id, WolService $service): JsonResponse
    {
        return new JsonResponse($service->wake($id));
    }
}
