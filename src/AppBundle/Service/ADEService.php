<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;

class ADEService
{
  private $host;
  private $login;
  private $password;
  private $client;

  public function __construct(array $config)
  {
      $this->host = $config["host"];
      $this->login = $config["login"];
      $this->password = $config["password"];
      $this->client = new Client();
  }

  private function callADE($function, $detail, $query)
  {
      $response = $this->client->get($this->host, array(
          "query" => array_merge(
              array(
                  "function" => $function,
                  "detail"   => $detail,
                  "login"    => $this->login,
                  "password" => $this->password,
              ),
              $query
          )
      ));

      $xml = new \SimpleXMLElement($response->getBody());
      $json = json_encode($xml);
      
      return json_decode($json, true); 
  }

  public function getProjects()
  {
      return $this->callADE("getProjects", 5, []);
  }

  public function getResources($projectId)
  {
      return $this->callADE("getResources", 7, [
          "projectId" => $projectId
      ]);
  }
      
  public function getEvents($projectId, $resources, $date, $startDate, $endDate)
  {
      return $this->callADE("getEvents", 8, [
          "projectId" => $projectId,
          "resources" => $resources,
          "date"      => $date,
          "startDate" => $startDate,
          "endDate"   => $endDate
      ]);
  }

  public function getActivities($projectId, $resources)
  {
      return $this->callADE("getActivities", 17, [
          "projectId" => $projectId,
          "resources" => $resources
      ]);
  }
}
