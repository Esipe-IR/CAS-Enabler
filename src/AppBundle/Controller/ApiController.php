<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/api/token", name="api_token")
     */
    public function tokenAction()
    {
        $responseService = $this->get("response.service");
        $casUser = $this->getUser();

        if (!$casUser) {
            return $responseService->sendError(1);
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($casUser->getUsername());

        if (!$token) {
            return $responseService->sendError(2);
        }

        return $responseService->sendSuccess($token);
    }

    /**
     * @Route("/~vrasquie/cas/api/me", name="api_me")
     */
    public function meAction(Request $request)
    {
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            return $responseService->sendError(4);
        }
        
        $userService = $this->get("user.service");
        $user = $userService->getUser($jwt->uid);

        return $responseService->sendSuccess($user->toArray());
    }

    /**
     * @Route("/~vrasquie/cas/api/ldap/me", name="api_ldap_me")
     */
    public function ldapMeAction(Request $request)
    {
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            return $responseService->sendError(4);
        }

        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($jwt->uid);

        return $responseService->sendSuccess($ldapUser);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/raw", name="api_edt_raw")
     */
    public function edtRawAction(Request $request)
    {
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getRaw($request->query);

        return $responseService->sendSuccess($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/resources", name="api_edt_resources")
     */
    public function edtResourcesAction(Request $request)
    {
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getResources(
            $request->query->get("detail")
        );

        return $responseService->sendSuccess($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/activities/{resources}", name="api_edt_activities")
     */
    public function edtActivitiesAction(Request $request, $resources)
    {
        //1813,1806,1812,1811,1807,1640,5314
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getActivities($resources, $request->query->get("detail"));

        return $responseService->sendSuccess($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/events/{resources}", name="api_edt_events")
     */
    public function edtEventsAction(Request $request, $resources)
    {
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getEvents(
            $resources,
            $request->query->get("date"),
            $request->query->get("detail")
        );

        return $responseService->sendSuccess($xml);
    }
}
