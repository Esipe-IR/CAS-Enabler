<?php

namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UserType extends ObjectType
{
    public function __construct()
    {
        $config = array(
            "name" => "User",
            "fields" => function() {
                return array(
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
                    "status" => array(
                        "type" => Type::boolean()
                    ),
                    "homeDir" => array(
                        "type" => Type::string()
                    )
                );
            }
        );
        
        parent::__construct($config);
    }
}
