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
        
        $r = new ResourceType();
        $a = new ActivityType();
        $e = new EventType();

        $config = array(
            "name" => "Query",
            "fields" => array(
                "resource" => array(
                    "type" => $r,
                    "args" => array(
                        "id" => array(
                            "type" => Type::nonNull(Type::int())
                        )
                    ),
                    "resolve" => function($root, $args) {
                        $arr = $this->calendarService->getResources();
                        return $arr[$args["id"]];
                    }
                ),
                "resources" => array(
                    "type" => Type::listOf($r),
                    "resolve" => function() {
                        return $this->calendarService->getResources();
                    }
                ),
                "activities" => array(
                    "type" => Type::listOf($a),
                    "resolve" => function() {
                        return true;
                    }
                ),
                "events" => array(
                    "type" => Type::listOf($e),
                    "args" => array(
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
                        return $this->calendarService->getEvents($args);
                    }
                ),
                "user" => array(
                    "type" => new UserType(),
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
