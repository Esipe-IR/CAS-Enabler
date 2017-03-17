<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;

/**
 * Class RouterListener
 * @package AppBundle\EventListener
 */
class RouterListener implements EventSubscriberInterface
{
    private $baseUrl;
    private $env;

    /**
     * RouterListener constructor.
     * @param $baseUrl
     * @param $env
     */
    public function __construct($baseUrl, $env) {
        $this->baseUrl = $baseUrl;
        $this->env = $env;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->env === "dev" && $event->isMasterRequest()) {
            //$r = $event->getRequest();
            //$uri = $this->baseUrl . $r->server->get("REQUEST_URI");
            //$r->server->set("REQUEST_URI", $uri);

            //$r->initialize($r->query->all(), $r->request->all(), $r->attributes->all(), $r->cookies->all(), $r->files->all(), $r->server->all(), $r->getContent());
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 33)),
        );
    }
}
