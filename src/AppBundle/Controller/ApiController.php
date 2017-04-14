<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/~vrasquie/u/api/token", name="api_token")
     */
    public function tokenAction()
    {
        $type = "receiveToken";
        $responseService = $this->get("response.service");
        $casUser = $this->getUser();

        if (!$casUser) {
            return $responseService->sendError($type, 1);
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($casUser->getUsername());

        if (!$token) {
            return $responseService->sendError($type, 2);
        }

        return $responseService->sendSuccess($type, $token);
    }

    /**
     * @Route("/~vrasquie/u/api/me", name="api_me")
     */
    public function meAction(Request $request)
    {
        $type = "receiveUser";
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            $r = $responseService->sendError($type, 4);
            $r->headers->set("Access-Control-Allow-Origin", "*");

            return $r;
        }
        
        $userService = $this->get("user.service");
        $user = $userService->getUser($jwt->uid);

        $r = $responseService->sendSuccess($type, $user->toArray());
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }

    /**
     * @Route("/~vrasquie/u/api/me/ldap", name="api_ldap_me")
     */
    public function ldapMeAction(Request $request)
    {
        $type = "receiveLdapUser";
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            $r = $responseService->sendError($type, 4);
            $r->headers->set("Access-Control-Allow-Origin", "*");

            return $r;
        }

        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($jwt->uid);

        $r = $responseService->sendSuccess($type, $ldapUser);
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }

    /**
     * @Route("/~vrasquie/u/api/calendar/raw", name="api_edt_raw")
     */
    public function edtRawAction(Request $request)
    {
        $type = "receiveCalendarRaw";
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getRaw($request->query);

        $r = $responseService->sendSuccess($type, $xml);
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }

    /**
     * @Route("/~vrasquie/u/api/calendar/resources", name="api_edt_resources")
     */
    public function edtResourcesAction(Request $request)
    {
        $type = "receiveCalendarResources";
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getResources(
            $request->query->get("detail")
        );

        $r = $responseService->sendSuccess($type, $xml);
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }

    /**
     * @Route("/~vrasquie/u/api/calendar/activities/{resources}", name="api_edt_activities")
     */
    public function edtActivitiesAction(Request $request, $resources)
    {
        //1813,1806,1812,1811,1807,1640,5314
        $type = "receiveCalendarActivities";
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getActivities($resources, $request->query->get("detail"));

        $r = $responseService->sendSuccess($type, $xml);
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }

    /**
     * @Route("/~vrasquie/u/api/calendar/events/{resources}", name="api_edt_events")
     */
    public function edtEventsAction(Request $request, $resources)
    {
        $type = "receiveCalendarResources";
        $responseService = $this->get("response.service");
        $edtService = $this->get("edt.service");
        $xml = $edtService->getEvents(
            $resources,
            $request->query->get("date"),
            $request->query->get("detail")
        );

        $r = $responseService->sendSuccess($type, $xml);
        $r->headers->set("Access-Control-Allow-Origin", "*");

        return $r;
    }
}
