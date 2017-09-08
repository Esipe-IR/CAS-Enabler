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
namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class EventType
 */
class EventType extends ObjectType
{
    /**
     * EventType constructor.
     */
    public function __construct()
    {
        $config = [
            "name" => "Event",
            "fields" => function () {
                return [
                    "id" => [
                        "type" => Type::nonNull(Type::string()),
                    ],
                    "activityId" => [
                        "type" => Type::string(),
                    ],
                    "name" => [
                        "type" => Type::string(),
                    ],
                    "startHour" => [
                        "type" => Type::string(),
                    ],
                    "endHour" => [
                        "type" => Type::string(),
                    ],
                    "date" => [
                        "type" => Type::string(),
                    ],
                    "week" => [
                        "type" => Type::string(),
                    ],
                    "color" => [
                        "type" => Type::string(),
                    ],
                    "lastUpdate" => [
                        "type" => Type::string(),
                    ],
                    "instructor" => [
                        "type" => Type::string(),
                    ],
                    "classroom" => [
                        "type" => Type::string(),
                    ],
                    "class" => [
                        "type" => Type::listOf(Type::string()),
                    ],
                ];
            },
        ];

        parent::__construct($config);
    }
}
