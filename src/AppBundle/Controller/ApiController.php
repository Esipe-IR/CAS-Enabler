<?php

namespace AppBundle\Controller;

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
            $tmpUser = new User();
            $tmpUser->setUid($userName);

            $em->persist($tmpUser);
            $em->flush();
        }
    }

    private function getLdapUser($userName)
    {
        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($userName);
        
        return $ldapService->transformToUser($ldapUser);
    }

    /**
     * @Route("/~vrasquie/cas/api/service", name="api_service")
     */
    public function serviceAction(Request $request)
    {
        $responseService = $this->get("response.service");
        $casUser = $this->get('security.token_storage')->getToken()->getUser();

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $casUser = $casUser->getUsername();
        $service = $request->query->get("service");
        $callback = $request->query->get("callback");
        
        if (!$service) {
            return $responseService->sendError(2, "Undefined service", $callback);
        }

        $em = $this->getDoctrine()->getManager();
        $this->checkIfExist($casUser);

        $isAllow = $em->getRepository("AppBundle:Service")->isAllow($casUser);

        if (!$isAllow) {
            return $responseService->sendError(3, "Unallowed service", $callback);
        }

        $user = $this->getLdapUser($casUser);

        $askService = $this->get("ask.service");
        $response = $askService->ask($service, $user);

        return $responseService->sendSuccess($response, $callback);
    }

    /**
     * @Route("/~vrasquie/cas/api/user", name="api_user")
     */
    public function userAction(Request $request)
    {
        $casUser = $this->get('security.token_storage')->getToken()->getUser();
        $callback = $request->query->get("callback");
        $responseService = $this->get("response.service");

        if (!$casUser) {
            return $responseService->sendError(1, "Not connected", $callback);
        }

        $this->checkIfExist($casUser->getUsername());
        $user = $this->getLdapUser($casUser->getUsername());

        return $responseService->sendSuccess($user->toArray(), $callback);
    }
}
