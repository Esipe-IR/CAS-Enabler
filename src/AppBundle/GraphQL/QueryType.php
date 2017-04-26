<?php

namespace AppBundle\GraphQL;

use AppBundle\Service\CalendarService;
use AppBundle\Service\JWTService;
use AppBundle\Service\UserService;
use GraphQL\Type\Definition\ObjectType;

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

        $config = array(
            "name" => "Query",
            "fields" => array(
                "calendar" => array(
                    "type" => new CalendarType($this->calendarService),
                    "resolve" => function() {
                        return true;
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
