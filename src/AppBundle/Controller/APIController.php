<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class APIController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/token", name="token")
     */
    public function tokenAction(Request $request)
    {
        $casUser = $this->getUser();
        $responseService = $this->get("response.service");

        if (!$casUser) {
            return $responseService->sendError(1);
        }

        $userService = $this->get("user.service");
        $user = $userService->getUserByUid($casUser->getUsername());

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($user);

        if (!$token) {
            return $responseService->sendError(2);
        }

        return $responseService->sendSuccess($token);
    }

    /**
     * @Route("/~vrasquie/cas/edt", name="edt")
     */
    public function edtAction(Request $request)
    {
        //https://edt.u-pem.fr/jsp/custom/modules/plannings/anonymous_cal.jsp?resources=1813,1806,1812,1811,1807,1640,5314&projectId=19&calType=ical&nbWeeks=4&sqlMode=true

    }
}
