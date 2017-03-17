<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Service;
use AppBundle\Form\ServiceAllowType;
use AppBundle\Form\ServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/auth", name="auth")
     */
    public function authAction(Request $request)
    {
        if ($request->query->get("redirect")) {
            return $this->redirectToRoute($request->query->get("redirect"), array("uid" => $request->query->get("uid")));
        }

        return new Response("Success! You are now connected. You can close this window.");
    }

    /**
     * @Route("/~vrasquie/cas/service/create", name="service_create")
     */
    public function createAction(Request $request) {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uidService = $this->get("uid.service");
            $uid = $uidService->generate();
            $service->setUid($uid);

            $em = $this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();
            
            return $this->redirectToRoute("service_success", array("uid" => $service->getUid()));
        }

        return $this->render('pages/create.html.twig', array(
            "form" => $form->createView()
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/connect", name="service_connect")
     */
    public function connectAction(Request $request, $uid)
    {
        $casUser = $this->getUser();

        if (!$casUser) {
            return $this->redirectToRoute("auth", array(
                "uid" => $uid,
                "redirect" => "service_connect"
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $this->render('actions/connect.html.twig', array(
                "action" => 2
            ));
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if (!$isAllow) {
            return $this->redirectToRoute("service_allow", array(
                "uid" => $uid,
                "redirect" => "service_connect"
            ));
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($service, $user);

        if (!$token) {
            return $this->render('actions/connect.html.twig', array(
                "action" => 4
            ));
        }

        return $this->render('actions/connect.html.twig', array(
            "action" => 0,
            "token" => $token
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/success", name="service_success")
     */
    public function successAction(Request $request, $uid)
    {
        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));
        
        if (!$service) {
            return $this->redirectToRoute("home");
        }
        
        $rsakeyService = $this->get("rsakey.service");
        
        if (!$rsakeyService->isValid($service)) {
            return $this->redirectToRoute("home");
        }
        
        $publicKey = $rsakeyService->generate($service);
        
        return $this->render('pages/success.html.twig', array(
            "service" => $service,
            "publicKey" => $publicKey
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/allow", name="service_allow")
     */
    public function allowAction(Request $request, $uid)
    {
        $casUser = $this->getUser();

        if (!$casUser) {
            return $this->redirectToRoute("auth");
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $this->redirectToRoute("home");
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if ($isAllow) {
            return $this->redirectToRoute("home");
        }

        $form = $this->createForm(ServiceAllowType::class, null);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->getData()["allow"]) {
                return $this->redirectToRoute("service_allow", array("uid" => $uid));
            }

            $user->addService($service);
            $service->addUser($user);
            $em->flush();
            
            return $this->redirectToRoute($request->query->get("redirect"), array(
                "uid" => $uid
            ));
        }
        
        return $this->render('pages/allow.html.twig', array(
            "service" => $service,
            "form" => $form->createView()
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{uid}/token/{token}", name="service_verify")
     */
    public function serviceVerifyAction(Request $request, $uid, $token)
    {
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $jwt = $jwtService->verify($service, $token);

        if (!$jwt) {
            return $responseService->sendError(5, "Not valid token", $callback);
        }

        return $responseService->sendSuccess($jwt, $callback);
    }
}
