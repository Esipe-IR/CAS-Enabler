<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use AppBundle\Entity\User;

class AskService
{
    public function ask($service, User $user)
    {
        $client = new Client();
        $url = "https://" . $service . "?user=" . json_encode($user->toArray());
        $request = new Request("GET", $url);

        try {
            $response = $client->send($request);
        } catch (Exception $e) {
            return "{}";
        }

        return (string) $response->getBody();
    }
}
