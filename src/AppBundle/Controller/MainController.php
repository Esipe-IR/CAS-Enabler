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
        $casService = $this->get("cas.service");
        $auth = $casService->check();
        $service = $request->query->get("service");

        if (!$auth) {
        }

        if (!$service) {
        }

        $casUser = $casService->getUser();

        return $this->render('default/service.allow.html.twig');
    }

    private function rrmdir($dir) {
        $objects = scandir($dir); 

        foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
                if (is_dir($dir."/".$object)) {
                    $this->rrmdir($dir."/".$object);
                }
                else {
                    unlink($dir."/".$object);
                } 
            }
        }

        rmdir($dir);
    }

    /**
     * @Route("/~vrasquie/cas/flush", name="flush")
     */
    public function flushAction(Request $request)
    {
        $this->rrmdir($this->get('kernel')->getRootDir() . "/../var/cache");

        return new Response("Delete");
    }
}
