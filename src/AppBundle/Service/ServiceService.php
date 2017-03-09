<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class ServiceService
{
    private $client;
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->client = new Client();
    }
    
    public function ask(Service $service, User $user)
    {
        $url = $service->getUrl() . "?user=" . json_encode($user->toArray());
        $request = new Request("GET", $url);
        
        $response = $this->client->send($request);

        return (string) $response->getBody();
    }
    
    public function allow(Service $service, User $user)
    {
        $user->addService($service);
        $service->addUser($user);
        $this->em->flush();
    }
    
    public function create()
    {
    }
}
