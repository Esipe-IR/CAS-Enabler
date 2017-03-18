<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class APIController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/token", name="token")
     */
    public function tokenAction(Request $request)
    {
        $casUser = $this->getUser();
        $responseService = $this->get("response.service");

        if (!$casUser) {
            return $responseService->sendError(1);
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($user);

        if (!$token) {
            return $responseService->sendError(2);
        }

        return $responseService->sendSuccess($token);
    }

    /**
     * @Route("/~vrasquie/cas/user", name="service_user")
     */
    public function userAction(Request $request)
    {
        $token = $request->headers->get("token");
        $service = $request->headers->get("service");
        $responseService = $this->get("response.service");

        if (!$token) {
            return $responseService->sendError(3);
        }

        if (!$service) {
            return $responseService->sendError(7);
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->findOneBy(array(
            "uid" => $service
        ));

        if (!$service) {
            return $responseService->sendError(8);
        }

        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($token);

        if (!$jwt) {
            return $responseService->sendError(4);
        }

        $uid = $jwtService->decodeUid($jwt->uid);

        if (!$uid) {
            return $responseService->sendError(10);
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($uid);

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($service->getId(), $user->getId());

        if (!$isAllow) {
            return $responseService->sendError(9);
        }
        
        $usr = $jwtService->encodeUser($service, $user);
        
        if (!$usr) {
            return $responseService->sendError(11);
        }

        return $responseService->sendSuccess($usr);
    }
}
