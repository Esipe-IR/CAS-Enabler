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
     * @param bool $status
     * @param int $code
     * @param string $data
     * @param string $error
     * @return JsonResponse
     * @throws \Exception
     */
    public function send($status, $code, $data, $error)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'status'    => $status,
            'code'      => $code,
            'data'      => $data,
            'error'     => $error
        ));

        return $response;
    }

    /**
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($code)
    {
        $error = $this->errorMapping[$code];
        return $this->send(false, $code, null, $error);
    }

    /**
     * @param string $data
     * @return JsonResponse
     */
    public function sendSuccess($data)
    {
        return $this->send(true, 0, $data, null);
    }
}
