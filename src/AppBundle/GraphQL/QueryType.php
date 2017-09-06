<?php

namespace AppBundle\GraphQL;

use AppBundle\Service\CalendarService;
use AppBundle\Service\JWTService;
use AppBundle\Service\UserService;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryType extends ObjectType
{
    private $calendarService;
    private $jwtService;
    private $userService;

    public function __construct(
        CalendarService $calendarService,
        JWTService $jwtService,
        UserService $userService
    ) {
        $this->calendarService = $calendarService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;
        
        $p = new ProjectType();
        $r = new ResourceType();
        $a = new ActivityType();
        $e = new EventType();
        $u = new UserType();

        $config = array(
            "name" => "Query",
            "fields" => array(
                "projects" => array(
                    "type" => Type::listOf($p),
                    "resolve" => function() {
                        return $this->calendarService->getProjects();
                    }
                ),
                "resource" => array(
                    "type" => $r,
                    "args" => array(
                        "projectId" => array(
                            "type" => Type::nonNull(Type::int())
                        ),
                        "id" => array(
                            "type" => Type::nonNull(Type::int())
                        )
                    ),
                    "resolve" => function($root, $args) {
                        $arr = $this->calendarService->getResources($args["projectId"]);

                        return $arr[$args["id"]];
                    }
                ),
                "resources" => array(
                    "type" => Type::listOf($r),
                    "args" => array(
                        "projectId" => array(
                            "type" => Type::nonNull(Type::int())
                        )
                    ),
                    "resolve" => function($root, $args) {
                        return $this->calendarService->getResources($args["projectId"]);
                    }
                ),
                "events" => array(
                    "type" => Type::listOf($e),
                    "args" => array(
                        "projectId" => array(
                            "type" => Type::nonNull(Type::int())
                        ),
                        "resources" => array(
                            "type" => Type::nonNull(Type::string())
                        ),
                        "date" => array(
                            "type" => Type::string()
                        ),
                        "startDate" => array(
                            "type" => Type::string()
                        ),
                        "endDate" => array(
                            "type" => Type::string()
                        )
                    ),
                    "resolve" => function($root, $args) {
                        $date = null;
                        $startDate = null;
                        $endDate = null;

                        if (isset($args["date"])) {
                            $date = $args["date"];
                        }

                        if (isset($args["startDate"])) {
                            $startDate = $args["startDate"];
                        }

                        if (isset($args["endDate"])) {
                            $endDate = $args["endDate"];
                        }

                        return $this->calendarService->getEvents(
                            $args["projectId"],
                            $args["resources"],
                            $date,
                            $startDate,
                            $endDate
                        );
                    }
                ),
                "activities" => array(
                    "type" => Type::listOf($a),
                    "resolve" => function() {
                        return true;
                    }
                ),
                "user" => array(
                    "type" => $u,
                    "resolve" => function($root) {
                        $jwt = $this->jwtService->decode($root["token"]);

                        if (!$jwt) {
                            throw new \Exception("Invalid token");
                        }

                        return $this->userService->getUser($jwt->uid)->toArray();
                    }
                )
            ),
        );

        parent::__construct($config);
    }
}
