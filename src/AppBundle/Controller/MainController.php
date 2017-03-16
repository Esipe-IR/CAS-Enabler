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
     * @Route("/", name="home")
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
     * @Route("/auth", name="auth")
     */
    public function authAction(Request $request)
    {
        return new Response("Success! You are now connected. You can close this window.");
    }

    /**
     * @Route("/service/register", name="service_register")
     */
    public function registerAction(Request $request)
    {
        return new Response();
    }

    /**
     * @Route("/service/{uid}/allow", name="service_allow")
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
     * @Route("/api/token", name="token_generate")
     */
    public function tokenGenerateAction(Request $request)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");
        $jwtService = $this->get("jwt.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $user = $userService->getUserByUid($casUser->getUsername());
        
        //TODO: Check service;
        var_dump($user->toArray());die;
        
        $token = $jwtService->generate($user);
        
        return $responseService->sendSuccess($token, $callback);
    }

    /**
     * @Route("/api/token/{token}", name="token_verify")
     */
    public function tokenVerifyAction(Request $request, $token)
    {
        $callback = $request->query->get("callback");
        
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");

        $user = $jwtService->verify($token);

        if (!$user) {
            return $responseService->sendError(5, "Not valid token", $callback);
        }
        
        return $responseService->sendSuccess($user, $callback);
    }

    /**
     * @Route("/service/{uid}/call", name="service_call")
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
     * @Route("/flush", name="flush")
     */
    public function flushAction(Request $request)
    {
        $dir = $this->get("kernel")->getRootDir() . "/../var/cache";

        $flushService = $this->get("flush.service");
        $flushService->removeDir($dir);

        return new Response("Delete");
    }
}
