<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/api/service/call/{id}", name="api_service")
     */
    public function serviceAction(Request $request, $id)
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
        $service = $em->getRepository("AppBundle:Service")->find($id);

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($id, $casUser);

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

        return $responseService->sendSuccess($user->toArray(), $callback);
    }
    
    private function allowAction(Request $request, $id)
    {
        $casUser = $this->getUser();
        $responseService = $this->get("response.service");
        $userService = $this->get("user.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected");
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->find($id);

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service");
        }

        $user = $userService->getUserByUid($casUser->getUsername());
        $this->get("service.service")->allow($service, $user);

        return $responseService->sendSuccess("Done");
    }
}
