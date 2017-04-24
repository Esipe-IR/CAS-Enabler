<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

class CalendarService
{
    private $host;
    private $projectId;
    private $login;
    private $password;
    private $resourcesMapping;
    private $rootDir;

    public function __construct($config, $rootDir)
    {
        $this->host = $config["host"];
        $this->projectId = $config["project_id"];
        $this->login = $config["login"];
        $this->password = $config["password"];
        $this->resourcesMapping = $config["resources_mapping"];
        $this->rootDir = $rootDir;
    }

    public function getResources()
    {
        $fs = new Filesystem();
        $filename = $this->rootDir . "/../var/api/resources-" . $this->projectId . ".json";

        if (!$fs->exists($filename)) {
            $xml = $this->getADEResources();
            
            $json = json_encode($xml);
            $arr = json_decode($json, true);

            var_dump(3);die;
            
            $array = array();
            foreach ($arr["resource"] as $k=>$r) {
                $array[$r["@attributes"]["id"]] = $r["@attributes"];
            }

            ksort($array);
            
            $json = json_encode($array);
            file_put_contents($filename, $json);
        } else {
            $json = file_get_contents($filename);
            $array = json_decode($json, true);
        }
        
        return $array;
    }

    public function getEvents($resources, array $request)
    {
        $xml = $this->getADEEvents($resources, $request);
        $json = json_encode($xml);
        $arr = json_decode($json, true);

        if (!isset($arr["event"])) {
            throw new \Exception("An error occured with your request. Maybe a date problem ?");
        }

        $array = array();

        foreach ($arr["event"] as $k=>$r) {
            $key = $r["@attributes"]["date"] . "-" . $r["@attributes"]["startHour"];

            $array[$key] = $r["@attributes"];

            foreach ($r["resources"]["resource"] as $re) {
                if ($re["@attributes"]["category"] == "trainee") {
                    $array[$key]["class"][] = $re["@attributes"]["name"];
                }

                if ($re["@attributes"]["category"] == "instructor") {
                    $array[$key]["instructor"] = $re["@attributes"]["name"];
                }

                if ($re["@attributes"]["category"] == "classroom") {
                    $array[$key]["classroom"] = $re["@attributes"]["name"];
                }
            }
        }

        ksort($array);

        return $array;
    }
    
    private function getADEResources()
    {
        $client = new Client();
        $response = $client->get($this->host, array(
            "query" => array(
                "function" => "getResources",
                "projectId" => $this->projectId,
                "detail" => 13,
                "login" => $this->login,
                "password" => $this->password
            )
        ));

        return new \SimpleXMLElement($response->getBody());
    }
    
    public function getActivities($resources, $detail = 17)
    {
        $client = new Client();
        $response = $client->get($this->host, array(
            "query" => array(
                "function" => "getActivities",
                "projectId" => $this->projectId,
                "resources" => $resources,
                "detail" => $detail ? $detail : 17,
                "login" => $this->login,
                "password" => $this->password
            )
        ));

        return new \SimpleXMLElement($response->getBody());
    }

    /**
     * @param $resources
     * @param array $request
     * @return \SimpleXMLElement
     */
    public function getADEEvents($resources, array $request)
    {
        $query = array_merge(array(
            "function" => "getEvents",
            "projectId" => $this->projectId,
            "resources" => $resources,
            "detail" => 8,
            "login" => $this->login,
            "password" => $this->password
        ), $request);

        $client = new Client();
        $response = $client->get($this->host, array(
            "query" => $query
        ));

        return new \SimpleXMLElement($response->getBody());
    }
}
