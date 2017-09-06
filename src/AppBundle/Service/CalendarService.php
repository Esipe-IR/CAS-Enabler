<?php

namespace AppBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class CalendarService
{
    private $adeService;
    private $rootDir;

    public function __construct(ADEService $adeService, $rootDir)
    {
        $this->adeService = $adeService;
        $this->rootDir = $rootDir;
    }

    public function getProjects()
    {
        $projects = $this->adeService->getProjects();

        if (!isset($projects["project"])) {
            throw new \Exception("ADE error");
        }

        $array = array();
        foreach ($projects["project"] as $k => $r) {
            $array[$k] = $r["@attributes"];
        }

        return $array;
    }

    public function getResources($projectId)
    {
        $fs = new Filesystem();
        $filename = $this->rootDir . "/../var/api/resources-" . $projectId . ".json";

        if ($fs->exists($filename)) {
            $json = file_get_contents($filename);
            return json_decode($json, true);   
        }

        $arr = $this->adeService->getResources($projectId);
        
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

    public function getEvents($projectId, $resources, $date, $startDate, $endDate)
    {
        $events = $this->adeService->getEvents($projectId, $resources, $date, $startDate, $endDate);

        if (!isset($events["event"])) {
            throw new \Exception("ADE error");
        }

        $array = array();
        foreach ($events["event"] as $k=>$r) {
            $array[$k] = $r["@attributes"];
            $array[$k]["name"] = str_replace("??", "é", $r["@attributes"]["name"]);

            foreach ($r["resources"]["resource"] as $re) {
                if ($re["@attributes"]["category"] == "trainee") {
                    $array[$k]["class"][] = $re["@attributes"]["name"];
                }

                if ($re["@attributes"]["category"] == "instructor") {
                    $array[$k]["instructor"] = $re["@attributes"]["name"];
                }

                if ($re["@attributes"]["category"] == "classroom") {
                    $array[$k]["classroom"] = $re["@attributes"]["name"];
                }
            }
        }
        
        return $array;
    }
}
