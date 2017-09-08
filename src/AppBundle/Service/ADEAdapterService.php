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

/**
 * Class ADEAdapterService
 */
class ADEAdapterService
{
    /**
     * @param array $raw
     *
     * @return array
     */
    public function adaptEvent($raw)
    {
        $event = $raw["@attributes"];
        $event["name"] = str_replace("??", "é", $event["name"]);

        foreach ($raw["resources"]["resource"] as $resource) {
            switch ($resource["@attributes"]["category"]) {
                case "trainee":
                    $event["class"][] = $resource["@attributes"]["name"];
                    break;
                case "instructor":
                    $event["instructor"] = $resource["@attributes"]["name"];
                    break;
                case "classroom":
                    $event["classroom"] = $resource["@attributes"]["name"];
                    break;
            }
        }

        return $event;
    }

    /**
     * @param array $raw
     *
     * @return array
     */
    public function adaptResource($raw)
    {
        $resource = $raw["@attributes"];
        $resource["name"] = str_replace("??", "é", $resource["name"]);

        return $resource;
    }

    /**
     * @param array $raw
     *
     * @return array
     */
    public function adaptProject($raw)
    {
        $project = $raw["@attributes"];

        return $project;
    }
}
