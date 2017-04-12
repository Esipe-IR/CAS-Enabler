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
        $casUser = $this->getUser();

        if (!$casUser) {}

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($user);

        $response = array(
            "type" => $token ? "success" : "error",
            "token" => $token,
            "code" => $token ? 0 : 2
        );

        return new JsonResponse($response);
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
        $user = $userService->getUserByUid($jwt->uid);

        return new JsonResponse($user->toArray());
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

        return new JsonResponse($ldapUser);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/raw", name="api_edt_raw")
     */
    public function edtRawAction(Request $request)
    {
        $edtService = $this->get("edt.service");
        $xml = $edtService->getRaw($request->query);

        return new JsonResponse($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/resources", name="api_edt_resources")
     */
    public function edtResourcesAction(Request $request)
    {
        $edtService = $this->get("edt.service");
        $xml = $edtService->getResources(
            $request->query->get("detail")
        );

        return new JsonResponse($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/activities/{resources}", name="api_edt_activities")
     */
    public function edtActivitiesAction(Request $request, $resources)
    {
        //1813,1806,1812,1811,1807,1640,5314
        $edtService = $this->get("edt.service");
        $xml = $edtService->getActivities($resources, $request->query->get("detail"));

        return new JsonResponse($xml);
    }

    /**
     * @Route("/~vrasquie/cas/api/edt/events/{resources}", name="api_edt_events")
     */
    public function edtEventsAction(Request $request, $resources)
    {
        $edtService = $this->get("edt.service");
        $xml = $edtService->getEvents(
            $resources,
            $request->query->get("date"),
            $request->query->get("detail")
        );

        return new JsonResponse($xml);
    }
}
