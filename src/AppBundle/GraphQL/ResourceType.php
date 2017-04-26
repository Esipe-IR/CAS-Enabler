<?php

namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ResourceType extends ObjectType
{
    public function __construct()
    {
        $config = array(
            "name" => "Resource",
            "fields" => function() {
                return array(
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
                );
            }
        );
        
        parent::__construct($config);
    }
}
