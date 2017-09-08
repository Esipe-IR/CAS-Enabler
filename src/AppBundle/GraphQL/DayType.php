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
 * Class DayType
 */
class DayType extends ObjectType
{
    /**
     * DayType constructor.
     */
    public function __construct()
    {
        $config = [
            "name" => "Day",
            "fields" => function () {
                return [
                    "date" => [
                        "type" => Type::nonNull(Type::string()),
                    ],
                    "events" => [
                        "type" => Type::listOf(QueryType::$EVENT),
                    ],
                ];
            },
        ];

        parent::__construct($config);
    }
}
