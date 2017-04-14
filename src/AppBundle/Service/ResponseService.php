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
    public function send($type, $code, $data, $error)
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

        return $response;
    }

    /**
     * @param string $type
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($type, $code)
    {
        $error = $this->errorMapping[$code];
        return $this->send($type, $code, null, $error);
    }

    /**
     * @param string $type
     * @param string $data
     * @return JsonResponse
     */
    public function sendSuccess($type, $data)
    {
        return $this->send($type, 0, $data, null);
    }
}
