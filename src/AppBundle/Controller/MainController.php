<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/", name="home")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/~vrasquie/cas/service/allow", name="service_allow")
     */
    public function serviceAllowAction(Request $request)
    {
        $service = $request->query->get("service");

        if (!$service) {
        }

        return $this->render('default/service.allow.html.twig');
    }

    /**
     * @Route("/~vrasquie/cas/flush", name="flush")
     */
    public function flushAction(Request $request)
    {
        $dir = $this->get("kernel")->getRootDir() . "/../var/cache";

        $flushService = $this->get("flush.service");
        $flushService->removeDir($dir);

        return new Response("Delete");
    }
}