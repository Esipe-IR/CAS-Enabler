<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ResponseService
 * @package AppBundle\Service
 */
class ResponseService
{
    /**
     * @param bool $status
     * @param int $code
     * @param string $data
     * @param string $error
     * @param string|null $callback
     * @return JsonResponse
     * @throws \Exception
     */
    public function send($status, $code, $data, $error, $callback = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'status'    => $status,
            'code'      => $code,
            'data'      => $data,
            'error'     => $error
        ));

        if ($callback) {
            $response->setCallback($callback);
        }

        return $response;
    }

    /**
     * @param int $code
     * @param string $error
     * @param string|null $callback
     * @return JsonResponse
     */
    public function sendError($code, $error, $callback = null)
    {
        return $this->send(false, $code, null, $error, $callback);
    }

    /**
     * @param string $data
     * @param string|null $callback
     * @return JsonResponse
     */
    public function sendSuccess($data, $callback = null)
    {
        return $this->send(true, 0, $data, null, $callback);
    }
}
