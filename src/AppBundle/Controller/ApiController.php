<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/api/service/call/{uid}", name="api_service")
     */
    public function serviceAction(Request $request, $uid)
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
     * @Route("/~vrasquie/cas/api/user/info", name="api_user")
     */
    public function userAction(Request $request)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $user = $userService->getUserByUid($casUser->getUsername());

        return $responseService->sendSuccess("debug", $callback);
    }
}
