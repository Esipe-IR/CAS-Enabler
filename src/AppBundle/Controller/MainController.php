<?php

namespace AppBundle\Controller;

use ICal\ICal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/cas/auth", name="auth")
     */
    public function authAction()
    {
        return $this->render('actions/close.html.twig');
    }

    /**
     * @Route("/~vrasquie/cas/connect", name="connect")
     */
    public function connectAction(Request $request)
    {
        return $this->render('actions/connect.html.twig', array(
            "connected" => $this->getUser() ? true : false,
            "target" => $request->query->get("target")
        ));
    }

    /**
     * @Route("/~vrasquie/cas/edt", name="edt")
     */
    public function edtAction(Request $request)
    {
        //1813,1806,1812,1811,1807,1640,5314
        $r = "resources=" . $request->query->get("resources");
        $p = "projectId=19";
        $t = "calType=ical";
        $n = "nbWeeks=6";
        $s = "sqlMode=true";
        $url = "https://edt.u-pem.fr/jsp/custom/modules/plannings/anonymous_cal.jsp?$r&$p&$t&$n&$s";

        $r = file_get_contents($url);
        $arr = explode("\n", $r);
        $i = new ICal($arr);

        $events = $i->eventsFromRange('first day of this month');
        $resp = array();

        foreach ($events as $event) {
            $dtstart = new \DateTime('@' . (int) $i->iCalDateToUnixTimestamp($event->dtstart));
            $resp[$dtstart->format("d_m")][] = $event;
        }

        dump($resp);die;
    }
}
