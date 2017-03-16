<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class APIController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/api/service/{uid}/token", name="service_token")
     */
    public function serviceTokenAction(Request $request, $uid)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");
        $jwtService = $this->get("jwt.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $userService->getUserByUid($casUser->getUsername());
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if (!$isAllow) {
            return $responseService->sendError(3, "Unallowed service", $callback);
        }

        $token = $jwtService->generate($user, $service);

        if (!$token) {
            return $responseService->sendError(4, "Fatal error token", $callback);
        }

        return $responseService->sendSuccess($token, $callback);
    }

    /**
     * @Route("/~vrasquie/cas/api/service/{uid}/token/{token}", name="service_verify")
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

    /**
     * @Route("/~vrasquie/cas/api/service/{uid}/rsa", name="service_rsa")
     */
    public function rsaAction(Request $request, $uid)
    {
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $uid));

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $rsakeyService = $this->get("rsakey.service");
        $pubKey = $rsakeyService->generate($service);

        return $responseService->sendSuccess($pubKey, $callback);        
    }
}
