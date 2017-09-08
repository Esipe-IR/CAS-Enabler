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
 * Class ResourceType
 */
class ResourceType extends ObjectType
{
    /**
     * ResourceType constructor.
     */
    public function __construct()
    {
        $config = [
            "name" => "Resource",
            "fields" => function () {
                return [
                    "id" => [
                        "type" => Type::nonNull(Type::int()),
                    ],
                    "name" => [
                        "type" => Type::string(),
                    ],
                    "path" => [
                        "type" => Type::string(),
                    ],
                    "category" => [
                        "type" => Type::string(),
                    ],
                    "lastUpdate" => [
                        "type" => Type::string(),
                    ],
                    "firstWeek" => [
                        "type" => Type::string(),
                    ],
                    "lastWeek" => [
                        "type" => Type::string(),
                    ],
                ];
            },
        ];

        parent::__construct($config);
    }
}
