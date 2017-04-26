<?php

namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ActivityType extends ObjectType
{
    public function __construct()
    {
        //1813,1806,1812,1811,1807,1640,5314
        $config = array(
            "name" => "Activity",
            "fields" => function() {
                return array(
                    "id" => array(
                        "type" => Type::nonNull(Type::string())
                    )
                );
            }
        );
        
        parent::__construct($config);
    }
}
