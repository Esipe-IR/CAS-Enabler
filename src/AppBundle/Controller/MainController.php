<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        return new Response("Success! You are now connected. You can close this window.");
    }

    /**
     * @Route("/~vrasquie/cas/service/register", name="service_register")
     */
    public function registerAction(Request $request)
    {
        return new Response();
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/allow", name="service_allow")
     */
    public function allowAction(Request $request, $uid)
    {
        $casUser = $this->getUser();
        $userService = $this->get("user.service");

        if (!$casUser) {
            return $this->redirectToRoute("auth");
        }

        $user = $userService->getUserByUid($casUser->getUsername());

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $this->redirectToRoute("home");
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if ($isAllow) {
            return $this->redirectToRoute("home");
        }
        
        if ($request->getMethod() === "POST") {
            $user->addService($service);
            $service->addUser($user);
            $em->flush();
        }
        
        return $this->render('default/allow.html.twig', array(
            "service" => $service
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/call", name="service_call")
     */
    public function callAction(Request $request, $uid)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $user = $userService->getUserByUid($casUser->getUsername());

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if (!$isAllow) {
            return $responseService->sendError(3, "Unallowed service", $callback);
        }

        try {
            $serviceService = $this->get("service.service");
            $response = $serviceService->ask($service, $user);
        } catch (\Exception $e) {
            return $responseService->sendError(4, "Service error", $callback);
        }

        return $responseService->sendSuccess($response, $callback);
    }

    /**
     * @Route("/~vrasquie/cas/api/user/info", name="api_user")
     */
    public function userAction(Request $request)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $user = $userService->getUserByUid($casUser->getUsername());

        return $responseService->sendSuccess("debug", $callback);
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
