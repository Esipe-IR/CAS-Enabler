<?php
/*
 * This file is part of UPEM API project.
 *
 * Based on https://github.com/Esipe-IR/UPEM-API
 *
 * (c) 2016-2017 Vincent Rasquier <vincent.rsbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AppBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CalendarService
 */
class CalendarService
{
    /**
     * @var ADEService
     */
    private $adeService;

    /**
     * @var ADEAdapterService
     */
    private $adapterService;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * CalendarService constructor.
     * @param ADEService $adeService
     * @param $rootDir
     */
    public function __construct(ADEService $adeService, ADEAdapterService $adapterService, $rootDir)
    {
        $this->adeService = $adeService;
        $this->adapterService = $adapterService;
        $this->rootDir = $rootDir;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getProjects()
    {
        $raw = $this->adeService->getProjects();

        if (!isset($raw["project"])) {
            throw new \Exception("ADE error");
        }

        $projects = [];
        foreach ($raw["project"] as $k => $r) {
            $projects[$k] = $this->adapterService->adaptProject($r);
        }

        return $projects;
    }

    /**
     * @param $projectId
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getResources($projectId)
    {
        $fs = new Filesystem();
        $filename = $this->rootDir."/../var/api/resources-".$projectId.".json";

        if ($fs->exists($filename)) {
            $json = file_get_contents($filename);

            return json_decode($json, true);
        }

        $raw = $this->adeService->getResources($projectId);

        if (!isset($raw["resource"])) {
            throw new \Exception("ADE error");
        }

        $resources = [];
        foreach ($raw["resource"] as $r) {
            $resources[$r["@attributes"]["id"]] = $this->adapterService->adaptResource($r);
        }

        ksort($resources);
        $json = json_encode($resources);
        file_put_contents($filename, $json);

        return $resources;
    }

    /**
     * @param int $projectId
     * @param int $id
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getResource($projectId, $id)
    {
        $raw = $this->adeService->getResource($projectId, $id);

        if (!isset($raw["resource"])) {
            throw new \Exception("ADE error");
        }

        return $this->adapterService->adaptResource($raw["resource"][0]);
    }

    /**
     * @param int $projectId
     * @param string $resources
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getDays($projectId, $resources, $startDate, $endDate)
    {
        $raw = $this->adeService->getEvents($projectId, $resources, null, $startDate, $endDate);

        if (!isset($raw["event"])) {
            throw new \Exception("ADE error");
        }

        $days = [];
        foreach ($raw["event"] as $r) {
            $date = $r["@attributes"]["date"];
            $days[$date]["date"] = $date;
            $days[$date]["events"][] = $this->adapterService->adaptEvent($r);
        }

        return $days;
    }

    /**
     * @param int $projectId
     * @param string $resources
     * @param string $date
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getEvents($projectId, $resources, $startDate, $endDate)
    {
        $raw = $this->adeService->getEvents($projectId, $resources, null, $startDate, $endDate);

        if (!isset($raw["event"])) {
            throw new \Exception("ADE error");
        }

        $events = [];
        foreach ($raw["event"] as $k => $r) {
            $events[$k] = $this->adapterService->adaptEvent($r);
        }

        return $events;
    }
}
