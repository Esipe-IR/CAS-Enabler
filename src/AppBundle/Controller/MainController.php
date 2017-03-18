<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Service;
use AppBundle\Form\ServiceAllowType;
use AppBundle\Form\ServiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
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
            
            return $this->redirectToRoute("service_success", array(
                "uid" => $service->getUid(),
                "passphrase" => $service->getPassphrase()
            ));
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
            return $this->redirectToRoute("service_create");
        }
        
        $rsakeyService = $this->get("rsakey.service");
        
        if ($rsakeyService->isValid($service)) {
            return $this->redirectToRoute("service_create");
        }
        
        $privateKey = $rsakeyService->generate($service, $request->query->get("passphrase"));

        if (!$privateKey) {
            return $this->redirectToRoute("service_create");
        }
        
        return $this->render('pages/success.html.twig', array(
            "service" => $service,
            "privateKey" => $privateKey
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

        $form = $this->createForm(ServiceAllowType::class, null);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->getData()["allow"]) {
                return $this->redirectToRoute("service_allow", array("uid" => $uid));
            }

            $user->addService($service);
            $service->addUser($user);
            $em->flush();
            
            return $this->redirectToRoute($request->query->get("redirect"));
        }
        
        return $this->render('pages/allow.html.twig', array(
            "service" => $service,
            "form" => $form->createView()
        ));
    }
}
