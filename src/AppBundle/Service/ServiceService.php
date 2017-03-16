<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;
use AppBundle\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class ServiceService
 * @package AppBundle\Service
 */
class ServiceService
{
    private $client;

    /**
     * ServiceService constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param Service $service
     * @param User $user
     * @return string
     */
    public function ask(Service $service, User $user)
    {
        $url = $service->getUrl() . "?user=" . json_encode($user->toArray());
        $request = new Request("GET", $url);
        $response = $this->client->send($request);

        return (string) $response->getBody();
    }
}
