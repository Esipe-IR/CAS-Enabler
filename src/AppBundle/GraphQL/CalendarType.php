<?php

namespace AppBundle\GraphQL;

use AppBundle\Service\CalendarService;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CalendarType extends ObjectType
{
    private $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $r = new ResourceType();
        $a = new ActivityType();
        $e = new EventType();

        $this->calendarService = $calendarService;

        $config = array(
            "name" => "Calendar",
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
                )
            )
        );
        
        parent::__construct($config);
    }
}
