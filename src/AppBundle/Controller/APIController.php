<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class APIController extends Controller
{
    /**
     * @Route("/api/token", name="token_generate")
     */
    public function tokenGenerateAction(Request $request)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $em = $this->getDoctrine()->getManager();

        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");
        $jwtService = $this->get("jwt.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $user = $userService->getUserByUid($casUser->getUsername());
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array("uid" => $request->getHttpHost()));

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
     * @Route("/api/token/{token}", name="token_verify")
     */
    public function tokenVerifyAction(Request $request, $token)
    {
        $callback = $request->query->get("callback");

        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");

        $jwt = $jwtService->verify($token);

        if (!$jwt) {
            return $responseService->sendError(5, "Not valid token", $callback);
        }

        return $responseService->sendSuccess($jwt, $callback);
    }
}
