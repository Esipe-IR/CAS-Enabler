<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
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
        $user = $em->getRepository("AppBundle:User")->findOneBy(array("uid" => $casUser->getUsername()));

        if (!$user) {
            $tmpUser = new User();
            $tmpUser->setUid($casUser->getUsername());
            $em->persist($tmpUser);
            $em->flush();
        }

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($casUser->getUsername());

        if (!$isAllow) {
            return $this->sendError(3, "Unallowed service", $callback);
        }

        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($casUser->getUsername());
        $user = $ldapService->transformToUser($ldapUser);

        $askService = $this->get("ask.service");
        $response = $askService->ask($service, $user);

        return $this->sendSuccess($response, $callback);
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
            $tmpUser = new User();
            $tmpUser->setUid($casUser->getUsername());
            $em->persist($tmpUser);
            $em->flush();
        }

        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($casUser->getUsername());
        $user = $ldapService->transformToUser($ldapUser);

        return $this->sendSuccess($user->toArray(), $callback);
    }
}
