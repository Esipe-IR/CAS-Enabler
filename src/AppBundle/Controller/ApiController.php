<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/~vrasquie/core/api/token", name="api_token")
     */
    public function tokenAction()
    {
        $type = "receiveToken";
        $responseService = $this->get("response.service");
        $casUser = $this->getUser();

        if (!$casUser) {
            return $responseService->sendError($type, 1, false);
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($casUser->getUsername());

        if (!$token) {
            return $responseService->sendError($type, 2, false);
        }

        return $responseService->sendSuccess($type, $token, false);
    }

    /**
     * @Route("/~vrasquie/core/api/me", name="api_me")
     */
    public function meAction(Request $request)
    {
        $type = "receiveUser";
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            return $responseService->sendError($type, 4, true);
        }
        
        $userService = $this->get("user.service");
        $user = $userService->getUser($jwt->uid);

        return $responseService->sendSuccess($type, $user->toArray(), true);
    }

    /**
     * @Route("/~vrasquie/core/api/me/ldap", name="api_ldap_me")
     */
    public function ldapMeAction(Request $request)
    {
        $type = "receiveLdapUser";
        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            return $responseService->sendError($type, 4, true);
        }

        $ldapService = $this->get("ldap.service");
        $ldapUser = $ldapService->getUser($jwt->uid);

        return $responseService->sendSuccess($type, $ldapUser, true);
    }

    /**
     * @Route("/~vrasquie/core/api/calendar/raw", name="api_edt_raw")
     */
    public function edtRawAction(Request $request)
    {
        $type = "receiveCalendarRaw";
        $responseService = $this->get("response.service");
        $calendarService = $this->get("calendar.service");
        $xml = $calendarService->getRaw($request->query);

        return $responseService->sendSuccess($type, $xml, true);
    }

    /**
     * @Route("/~vrasquie/core/api/calendar/resources", name="api_edt_resources")
     */
    public function edtResourcesAction(Request $request)
    {
        $type = "receiveCalendarResources";
        $responseService = $this->get("response.service");
        $calendarService = $this->get("calendar.service");
        $xml = $calendarService->getResources($request->query->get("detail"));

        return $responseService->sendSuccess($type, $xml, true);
    }

    /**
     * @Route("/~vrasquie/core/api/calendar/activities/{resources}", name="api_edt_activities")
     */
    public function edtActivitiesAction(Request $request, $resources)
    {
        //1813,1806,1812,1811,1807,1640,5314
        $type = "receiveCalendarActivities";
        $responseService = $this->get("response.service");
        $calendarService = $this->get("calendar.service");
        $xml = $calendarService->getActivities($resources, $request->query->get("detail"));

        return $responseService->sendSuccess($type, $xml, true);
    }

    /**
     * @Route("/~vrasquie/core/api/calendar/events", name="api_edt_events")
     */
    public function edtEventsAction(Request $request)
    {
        $type = "receiveCalendarEvents";

        $responseService = $this->get("response.service");
        $jwtService = $this->get("jwt.service");
        $jwt = $jwtService->decode($request->headers->get("token"));

        if (!$jwt) {
            return $responseService->sendError($type, 4, true);
        }

        $userService = $this->get("user.service");
        $user = $userService->getUser($jwt->uid);

        $calendarService = $this->get("calendar.service");
        $resources = $calendarService->getResourcesByClass($user->getClass());
        $xml = $calendarService->getEvents($resources, $request->query->all());

        return $responseService->sendSuccess($type, $xml, true);
    }
}
