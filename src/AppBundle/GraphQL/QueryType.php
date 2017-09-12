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

use AppBundle\Service\CalendarService;
use AppBundle\Service\JWTService;
use AppBundle\Service\UserService;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class QueryType
 */
class QueryType extends ObjectType
{
    /**
     * @var CalendarService
     */
    private $calendarService;

    /**
     * @var JWTService
     */
    private $jwtService;

    /**
     * @var UserService
     */
    private $userService;

    public static $PROJECT;
    public static $RESOURCE;
    public static $EVENT;
    public static $DAY;
    public static $ACTIVITY;
    public static $USER;

    /**
     * QueryType constructor.
     * @param CalendarService $calendarService
     * @param JWTService $jwtService
     * @param UserService $userService
     */
    public function __construct(CalendarService $calendarService, JWTService $jwtService, UserService $userService)
    {
        $this->calendarService = $calendarService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;

        self::$PROJECT = new ProjectType();
        self::$RESOURCE = new ResourceType($calendarService);
        self::$EVENT = new EventType();
        self::$DAY = new DayType();
        self::$ACTIVITY = new ActivityType();
        self::$USER = new UserType();

        $config = [
            "name" => "Query",
            "fields" => [
                "projects" => [
                    "type" => Type::listOf(self::$PROJECT),
                    "resolve" => function () {
                        return $this->calendarService->getProjects();
                    },
                ],
                "resource" => [
                    "type" => self::$RESOURCE,
                    "args" => [
                        "projectId" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                        "id" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                    ],
                    "resolve" => function ($root, $args) {
                        return $this->calendarService->getResource(
                            $args["projectId"],
                            $args["id"]
                        );
                    },
                ],
                "resources" => [
                    "type" => Type::listOf(self::$RESOURCE),
                    "args" => [
                        "projectId" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                    ],
                    "resolve" => function ($root, $args) {
                        return $this->calendarService->getResources($args["projectId"]);
                    },
                ],
                "days" => [
                    "type" => Type::listOf(self::$DAY),
                    "args" => [
                        "projectId" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                        "resources" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                        "startDate" => [
                            "type" => Type::nonNull(Type::string()),
                        ],
                        "endDate" => [
                            "type" => Type::nonNull(Type::string()),
                        ],
                    ],
                    "resolve" => function ($root, $args) {
                        return $this->calendarService->getDays(
                            $args["projectId"],
                            $args["resources"],
                            $args["startDate"],
                            $args["endDate"]
                        );
                    },
                ],
                "events" => [
                    "type" => Type::listOf(self::$EVENT),
                    "args" => [
                        "projectId" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                        "resources" => [
                            "type" => Type::nonNull(Type::int()),
                        ],
                        "startDate" => [
                            "type" => Type::nonNull(Type::string()),
                        ],
                        "endDate" => [
                            "type" => Type::nonNull(Type::string()),
                        ],
                    ],
                    "resolve" => function ($root, $args) {
                        return $this->calendarService->getEvents(
                            $args["projectId"],
                            $args["resources"],
                            $args["startDate"],
                            $args["endDate"]
                        );
                    },
                ],
                "activities" => [
                    "type" => Type::listOf(self::$ACTIVITY),
                    "resolve" => function () {
                        return true;
                    },
                ],
                "user" => [
                    "type" => self::$USER,
                    "resolve" => function ($root) {
                        $jwt = $this->jwtService->decode($root["token"]);

                        if (!$jwt) {
                            throw new \Exception("Invalid token");
                        }

                        return $this->userService->getUser($jwt->uid)->toArray();
                    },
                ],
            ],
        ];

        parent::__construct($config);
    }
}
