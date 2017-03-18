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
            return $this->redirectToRoute($request->query->get("redirect"), array(
                "publicUid" => $request->query->get("publicUid")
            ));
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
            
            $publicUid = $uidService->generate();
            $service->setPublicUid($publicUid);

            $em = $this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();

            $rsakeyService = $this->get("rsakey.service");
            $privateKey = $rsakeyService->generate($service, $service->getPassphrase());

            if (!$privateKey) {
                return $this->redirectToRoute("service_create");
            }

            return $this->render('pages/success.html.twig', array(
                "service" => $service,
                "privateKey" => $privateKey
            ));
        }

        return $this->render('pages/create.html.twig', array(
            "form" => $form->createView()
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{publicUid}/connect", name="service_connect")
     */
    public function connectAction(Request $request, $publicUid)
    {
        $casUser = $this->getUser();

        if (!$casUser) {
            return $this->redirectToRoute("auth", array(
                "publicUid" => $publicUid,
                "redirect" => "service_connect"
            ));
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:Service");
        $service = $repo->findOneBy(array("publicUid" => $publicUid));

        if (!$service) {
            return $this->render('actions/connect.html.twig', array(
                "code" => 8,
                "token" => null
            ));
        }

        $isAllow = $repo->isAllow($service->getId(), $user->getId());

        if (!$isAllow) {
            return $this->redirectToRoute("service_allow", array(
                "publicUid" => $publicUid,
                "redirect" => "service_connect"
            ));
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($user);

        if (!$token) {
            return $this->render('actions/connect.html.twig', array(
                "code" => 2,
                "token" => null
            ));
        }

        return $this->render('actions/connect.html.twig', array(
            "code" => 0,
            "token" => $token
        ));
    }

    /**
     * @Route("/~vrasquie/cas/service/{publicUid}/allow", name="service_allow")
     */
    public function allowAction(Request $request, $publicUid)
    {
        $casUser = $this->getUser();

        if (!$casUser) {
            return $this->redirectToRoute("auth");
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(
            array("publicUid" => $publicUid)
        );

        if (!$service) {
            return $this->redirectToRoute("home");
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $form = $this->createForm(ServiceAllowType::class, null);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->getData()["allow"]) {
                return $this->redirectToRoute("service_allow", array("publicUid" => $publicUid));
            }

            $user->addService($service);
            $service->addUser($user);
            $em->flush();
            
            return $this->redirectToRoute($request->query->get("redirect"), array(
                "publicUid" => $publicUid
            ));
        }
        
        return $this->render('pages/allow.html.twig', array(
            "service" => $service,
            "form" => $form->createView()
        ));
    }
}
