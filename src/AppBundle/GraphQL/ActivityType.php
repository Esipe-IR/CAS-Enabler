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
 * Class ActivityType
 */
class ActivityType extends ObjectType
{
    /**
     * ActivityType constructor.
     */
    public function __construct()
    {
        $config = [
            "name" => "Activity",
            "fields" => function () {
                return [
                    "id" => [
                        "type" => Type::nonNull(Type::string()),
                    ],
                ];
            },
        ];

        parent::__construct($config);
    }
}
