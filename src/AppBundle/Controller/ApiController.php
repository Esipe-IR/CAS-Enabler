<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    private function sendError($code, $msg, $callback)
    {
        $response = new JsonResponse();
        $response->setStatusCode(400);
        $response->setData(array(
            'status' => false,
            'code' => $code,
            'data' => $msg
        ));

        if ($callback) {
            $response->setCallback($callback);
        }

        return $response;
    }

    private function sendSuccess($body, $callback)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'status' => true,
            'code' => 0,
            'data' => $body
        ));

        if ($callback) {
            $response->setCallback($callback);
        }

        return $response;
    }

    /**
     * @Route("/~vrasquie/cas/api/service", name="api_service")
     */
    public function serviceAction(Request $request)
    {
        $casUser = $this->get('security.token_storage')->getToken()->getUser();
        $service = $request->query->get("service");
        $callback = $request->query->get("callback");

        if (!$casUser) {
            return $this->sendError(1, "Not connected", $callback);
        }

        $ticket = $request->cookies->get("PHPSESSID");
        
        if (!$service) {
            return $this->sendError(2, "Undefined service", $callback);
        }

        $em = $this->getDoctrine()->getManager();
        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($casUser->getUsername());

        if (!$isAllow) {
            return $this->sendError(3, "Unallowed service", $callback);
        }

        return $this->sendSuccess("", $callback);
    }

    /**
     * @Route("/~vrasquie/cas/api/user", name="api_user")
     */
    public function userAction(Request $request)
    {
        $casUser = $this->get('security.token_storage')->getToken()->getUser();
        $callback = $request->query->get("callback");

        if (!$casUser) {
            return $this->sendError(1, "Not connected", $callback);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->findOneBy(array("uid" => $casUser->getUsername()));

        if (!$user) {
            $ldapService = $this->get("ldap.service");
            $ldapUser = $ldapService->getUser($casUser->getUsername());
            $user = $ldapService->transformToUser($ldapUser);
            $em->persist($user);
            $em->flush();
        }

        return $this->sendSuccess($user->toArray(), $callback);
    }
}
