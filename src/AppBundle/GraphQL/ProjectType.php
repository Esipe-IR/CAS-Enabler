<?php

namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProjectType extends ObjectType
{
    public function __construct()
    {
        $config = array(
            "name" => "Project",
            "fields" => function() {
                return array(
                    "id" => array(
                        "type" => Type::nonNull(Type::string())
                    ),
                    "name" => array(
                        "type" => Type::string()
                    ),
                    "uid" => array(
                        "type" => Type::int()
                    ),
                    "version" => array(
                        "type" => Type::int()
                    ),
                    "loaded" => array(
                        "type" => Type::boolean()
                    ),
                    "nbConnected" => array(
                        "type" => Type::int()
                    )
                );
            }
        );

        parent::__construct($config);
    }
}
