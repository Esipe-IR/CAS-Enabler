<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/core/auth", name="auth")
     */
    public function authAction()
    {
        return $this->render('actions/close.html.twig');
    }

    /**
     * @Route("/~vrasquie/core/connect", name="connect")
     */
    public function connectAction(Request $request)
    {
        return $this->render('actions/connect.html.twig', array(
            "target" => $request->query->get("target")
        ));
    }
}
