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

use GuzzleHttp\Client;

/**
 * Class ADEService
 */
class ADEService
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Client
     */
    private $client;

    /**
     * ADEService constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->host = $config["host"];
        $this->login = $config["login"];
        $this->password = $config["password"];
        $this->client = new Client();
    }

    /**
     * @return array
     */
    public function getProjects()
    {
        return $this->callADE("getProjects", 5, []);
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function getResources($projectId)
    {
        return $this->callADE("getResources", 7, [
            "projectId" => $projectId,
        ]);
    }

    /**
     * @param int $projectId
     * @param string $resources
     * @param string $date
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     */
    public function getEvents($projectId, $resources, $date, $startDate, $endDate)
    {
        return $this->callADE("getEvents", 8, [
            "projectId" => $projectId,
            "resources" => $resources,
            "date"      => $date,
            "startDate" => $startDate,
            "endDate"   => $endDate,
        ]);
    }

    /**
     * @param int $projectId
     * @param string $resources
     *
     * @return array
     */
    public function getActivities($projectId, $resources)
    {
        return $this->callADE("getActivities", 17, [
            "projectId" => $projectId,
            "resources" => $resources,
        ]);
    }

    /**
     * @param string $function
     * @param int $detail
     * @param array $query
     *
     * @return array
     */
    private function callADE($function, $detail, $query)
    {
        $response = $this->client->get($this->host, [
            "query" => array_merge([
                "function" => $function,
                "detail"   => $detail,
                "login"    => $this->login,
                "password" => $this->password,
            ], $query),
        ]);

        $xml = new \SimpleXMLElement($response->getBody());
        $json = json_encode($xml);

        return json_decode($json, true);
    }
}
