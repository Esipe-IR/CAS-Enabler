<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ResponseService
 * @package AppBundle\Service
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
     * @return JsonResponse
     * @throws \Exception
     */
    public function send($type, $code, $data, $error, $expose)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'type'      => $type,
            'code'      => $code,
            'data'      => $data,
            'error'     => $error,
            'scope'     => "UPEM-Api"
        ));

        if ($expose) {
            $response->headers->set("Access-Control-Allow-Origin", "*");
            $response->headers->set("Access-Control-Allow-Headers", "Token");
            $response->headers->set("Content-Type", "application/json;charset=UTF-8");
        }

        return $response;
    }

    /**
     * @param string $type
     * @param int $code
     * @param bool $expose
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
     * @return JsonResponse
     */
    public function sendSuccess($type, $data, $expose)
    {
        return $this->send($type, 0, $data, null, $expose);
    }
}
