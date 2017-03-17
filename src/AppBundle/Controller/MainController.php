<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Service;
use AppBundle\Form\ServiceType;
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

        return $this->render('pages/index.html.twig', array(
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
            var_dump($service);die;

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
}
