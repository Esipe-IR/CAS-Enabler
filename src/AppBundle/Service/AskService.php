<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use AppBundle\Entity\User;

class AskService
{
    public function ask(Service $service, User $user)
    {
        $client = new Client();
        $url = $service->getUrl() . "?user=" . json_encode($user->toArray());
        $request = new Request("GET", $url);
        $response = $client->send($request);

        return (string) $response->getBody();
    }
}
