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
     * @Route("/~vrasquie/cas/connect", name="connect")
     */
    public function connectAction()
    {
        $casUser = $this->getUser();

        if (!$casUser) {
            return $this->redirectToRoute("auth", array(
                "redirect" => "connect"
            ));
        }
        
        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($user);

        $response = array(
            "token" => $token,
            "code" => $token ? 0 : 2
        );
        
        return $this->render('actions/connect.html.twig', array(
            "response" => $response
        ));
    }
}
