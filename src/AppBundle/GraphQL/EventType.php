<?php

namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class EventType extends ObjectType
{
    public function __construct()
    {
        $config = array(
            "name" => "Event",
            "fields" => function() {
                return array(
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
                );
            }
        );
        
        parent::__construct($config);
    }
}
