<?php
/*
 * This file is part of UPEM API project.
 *
 * Based on https://github.com/Esipe-IR/UPEM-API
 *
 * (c) 2016-2017 Vincent Rasquier <vincent.rsbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AppBundle\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class UserType
 */
class UserType extends ObjectType
{
    /**
     * UserType constructor.
     */
    public function __construct()
    {
        $config = [
            "name" => "User",
            "fields" => function () {
                return [
                    "uid" => [
                        "type" => Type::string(),
                    ],
                    "name" => [
                        "type" => Type::string(),
                    ],
                    "lastname" => [
                        "type" => Type::string(),
                    ],
                    "email" => [
                        "type" => Type::string(),
                    ],
                    "etuId" => [
                        "type" => Type::int(),
                    ],
                    "status" => [
                        "type" => Type::boolean(),
                    ],
                    "homeDir" => [
                        "type" => Type::string(),
                    ],
                ];
            },
        ];

        parent::__construct($config);
    }
}
