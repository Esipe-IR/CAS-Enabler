<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}
