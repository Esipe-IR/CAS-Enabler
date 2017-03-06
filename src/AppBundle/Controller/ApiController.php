<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    private function checkIfExist($userName)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->findOneBy(array("uid" => $userName));

        if (!$user) {
            $user = new User();
            $user->setUid($userName);

            $em->persist($user);
            $em->flush();
        }

        return $user;
    }

    private function getLdapUser($userName)
    {
        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($userName);
        
        return $ldapService->transformToUser($ldapUser);
    }

    /**
     * @Route("/~vrasquie/cas/api/service/call/{id}", name="api_service")
     */
    public function serviceAction(Request $request, $id)
    {
        $casUser = $this->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }
        
        $casUser = $casUser->getUsername();
        $this->checkIfExist($casUser);

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->find($id);

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service", $callback);
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($id, $casUser);

        if (!$isAllow) {
            return $responseService->sendError(3, "Unallowed service", $callback);
        }

        $user = $this->getLdapUser($casUser);

        try {
            $askService = $this->get("ask.service");
            $response = $askService->ask($service, $user);
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

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $casUser = $casUser->getUsername();
        $this->checkIfExist($casUser);
        
        $user = $this->getLdapUser($casUser);

        return $responseService->sendSuccess($user->toArray(), $callback);
    }

    /**
     * @Route("/~vrasquie/cas/api/service/allow/{id}", name="api_allow")
     */
    private function allowAction(Request $request, $id)
    {
        $casUser = $this->getUser();
        $responseService = $this->get("response.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected");
        }

        $em = $this->getDoctrine()->getManager();
        $service = $em->getRepository("AppBundle:Service")->find($id);

        if (!$service) {
            return $responseService->sendError(2, "Nonexistent service");
        }

        $casUser = $casUser->getUsername();
        $user = $this->checkIfExist($casUser);
        $user->addService($service);
        $em->flush();

        $responseService->sendSuccess("Done");
    }
}
