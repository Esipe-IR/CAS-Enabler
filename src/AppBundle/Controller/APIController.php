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

        $token = $jwtService->generate($service, $user);

        if (!$token) {
            return $responseService->sendError(4, "Fatal error token", $callback);
        }

        return $responseService->sendSuccess($token, $callback);
    }
}
