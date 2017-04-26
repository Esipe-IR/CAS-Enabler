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

            if (!isset($arr["resource"])) {
                throw new \Exception("ADE error");
            }
            
            $array = array();

            foreach ($arr["resource"] as $k=>$r) {
                $array[$r["@attributes"]["id"]] = $r["@attributes"];
                $array[$r["@attributes"]["id"]]["name"] = str_replace("??", "é", $r["@attributes"]["name"]);
            }

            ksort($array);
            $json = json_encode($array);
            file_put_contents($filename, $json);

            return $array;
        }

        $json = file_get_contents($filename);

        return json_decode($json, true);
    }
    
    private function getADEResources()
    {
        $client = new Client();

        $response = $client->get($this->host, array(
            "query" => array(
                "function" => "getResources",
                "projectId" => $this->projectId,
                "detail" => 7,
                "login" => $this->login,
                "password" => $this->password
            )
        ));

        return new \SimpleXMLElement($response->getBody());
    }

    public function getEvents(array $request)
    {
        $xml = $this->getADEEvents($request);
        $json = json_encode($xml);
        $arr = json_decode($json, true);

        if (!isset($arr["event"])) {
            throw new \Exception("ADE error");
        }

        $array = array();

        foreach ($arr["event"] as $k=>$r) {
            $key = $r["@attributes"]["startHour"] . "-" . $r["@attributes"]["date"];

            $array[$key] = $r["@attributes"];

            $array[$key]["name"] = str_replace("??", "é", $r["@attributes"]["name"]);

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
    
    public function getADEEvents(array $request)
    {
        $query = array_merge(array(
            "function" => "getEvents",
            "projectId" => $this->projectId,
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
    
    public function getADEActivities($resources)
    {
        $client = new Client();

        $response = $client->get($this->host, array(
            "query" => array(
                "function" => "getActivities",
                "projectId" => $this->projectId,
                "resources" => $resources,
                "detail" => 17,
                "login" => $this->login,
                "password" => $this->password
            )
        ));

        return new \SimpleXMLElement($response->getBody());
    }
}
