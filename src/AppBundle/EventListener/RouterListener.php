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
    private $host;
    private $baseUrl;
    private $env;
    private $context;

    /**
     * RouterListener constructor.
     * @param $host
     * @param $baseUrl
     * @param $env
     * @param RequestContext $context
     */
    public function __construct(
        $host,
        $baseUrl,
        $env,
        RequestContext $context
    ) {
        $this->host = $host;
        $this->baseUrl = $baseUrl;
        $this->env = $env;
        $this->context = $context;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (preg_match('/_profiler|_wdt/i', $event->getRequest()->getRequestUri())) {
            return;
        }

        if ($this->env === "prod" && $event->isMasterRequest()) {
            $this->context->setHost($this->host);
            $this->context->setBaseUrl($this->baseUrl);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
        );
    }
}
