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
namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ResponseService
 */
class ResponseService
{
    private $errorMapping;

    /**
     * ResponseService constructor.
     * @param array $errorMapping
     */
    public function __construct(array $errorMapping)
    {
        $this->errorMapping = $errorMapping;
    }

    /**
     * @param string $type
     * @param int $code
     * @param string $data
     * @param string $error
     * @param bool $expose
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function send($type, $code, $data, $error, $expose)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData([
            'type'      => $type,
            'code'      => $code,
            'scope'     => "UPEM-API",
            'src'       => "CORE",
            'data'      => $data,
            'error'     => $error,
        ]);

        if ($expose) {
            $response->headers->set("Access-Control-Allow-Origin", "*");
            $response->headers->set("Access-Control-Allow-Headers", "Authorization");
            $response->headers->set("Content-Type", "application/json;charset=UTF-8");
        }

        return $response;
    }

    /**
     * @param string $type
     * @param int $code
     * @param bool $expose
     *
     * @return JsonResponse
     */
    public function sendError($type, $code, $expose)
    {
        $error = $this->errorMapping[$code];

        return $this->send($type, $code, null, $error, $expose);
    }

    /**
     * @param string $type
     * @param string $data
     * @param bool $expose
     *
     * @return JsonResponse
     */
    public function sendSuccess($type, $data, $expose)
    {
        return $this->send($type, 0, $data, null, $expose);
    }
}
