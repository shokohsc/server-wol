<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
      * @Route("/", methods={"GET"})
      * @Cache(expires="+5 minutes", public=true)
      */
    public function index()
    {
        return $this->render('base.html.twig');
    }
}
