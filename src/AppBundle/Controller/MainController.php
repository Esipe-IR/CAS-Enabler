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
        $em = $this->getDoctrine()->getManager();
        $services = $em->getRepository("AppBundle:Service")->findAll();
        
        if ($request->getMethod() === "POST") {
            
        }

        return $this->render('default/index.html.twig', array(
            "services" => $services
        ));
    }

    /**
     * @Route("/~vrasquie/cas/auth", name="auth")
     */
    public function authAction(Request $request)
    {
        return $this->redirectToRoute("api_service", array(
            "callback" => $request->query->get("callback"),
            "uid" => $request->query->get("uid")
        ));
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
