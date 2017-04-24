<?php

namespace AppBundle\Service;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class GraphQLService
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
    }

    public function getResourcesType() {
        return new ObjectType(array(
            "name" => "Resources",
            "fields" => array(
                "id" => array(
                    "type" => Type::nonNull(Type::int())
                ),
                "name" => array(
                    "type" => Type::string()
                ),
                "path" => array(
                    "type" => Type::string()
                ),
                "category" => array(
                    "type" => Type::string()
                ),
                "lastUpdate" => array(
                    "type" => Type::string()
                ),
                "firstWeek" => array(
                    "type" => Type::string()
                ),
                "lastWeek" => array(
                    "type" => Type::string()
                )
            )
        ));
    }

    public function getEventsType()
    {
        return new ObjectType(array(
            "name" => "Events",
            "fields" => array(
                "id" => array(
                    "type" => Type::nonNull(Type::string())
                ),
                "activityId" => array(
                    "type" => Type::string()
                ),
                "name" => array(
                    "type" => Type::string()
                ),
                "startHour" => array(
                    "type" => Type::string()
                ),
                "endHour" => array(
                    "type" => Type::string()
                ),
                "date" => array(
                    "type" => Type::string()
                ),
                "week" => array(
                    "type" => Type::string()
                ),
                "color" => array(
                    "type" => Type::string()
                ),
                "lastUpdate" => array(
                    "type" => Type::string()
                ),
                "instructor" => array(
                    "type" => Type::string()
                ),
                "classroom" => array(
                    "type" => Type::string()
                ),
                "class" => array(
                    "type" => Type::listOf(Type::string())
                )
            )
        ));
    }

    private function getActivitiesType()
    {
        return new ObjectType(array(
            "name" => "Activities",
            "fields" => array(
                "id" => array(
                    "type" => Type::nonNull(Type::string())
                )
            )
        ));
    }

    public function getCalendarType()
    {
        $resourcesInterface = $this->getResourcesType();
        $eventsInterface = $this->getEventsType();
        $activitiesInterface = $this->getActivitiesType();

        return new ObjectType(array(
            "name" => "Calendar",
            "fields" => array(
                "resource" => array(
                    "type" => $resourcesInterface,
                    "args" => array(
                        "id" => array(
                            "type" => Type::nonNull(Type::int())
                        )
                    ),
                    "resolve" => function($root, $args) {
                        $arr = $this->calendarService->getResources();
                        return $arr[$args["projectId"]];
                    }
                ),
                "resources" => array(
                    "type" => Type::listOf($resourcesInterface),
                    "resolve" => function() {
                        $arr = $this->calendarService->getResources();
                        return $arr;
                    }
                ),
                "events" => array(
                    "type" => Type::listOf($eventsInterface),
                    "args" => array(
                        "id" => array(
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
                        $args2 = $args;
                        unset($args2["id"]);

                        return $this->calendarService->getEvents($args["id"], $args2);
                    }
                ),
                "activities" => array(
                    "type" => Type::listOf($activitiesInterface),
                    "resolve" => function() {
                        return true;
                    }
                )
            ),
            "resolve" => function() {
                return true;
            }
        ));
    }

    public function getUserType()
    {
        return new ObjectType(array(
            "name" => "User",
            "fields" => array(
                "uid" => array(
                    "type" => Type::string()
                ),
                "name" => array(
                    "type" => Type::string()
                ),
                "lastname" => array(
                    "type" => Type::string()
                ),
                "email" => array(
                    "type" => Type::string()
                ),
                "etuId" => array(
                    "type" => Type::int()
                ),
                "class" => array(
                    "type" => Type::string()
                ),
                "status" => array(
                    "type" => Type::boolean()
                ),
                "homeDir" => array(
                    "type" => Type::string()
                )
            )
        ));
    }
}
