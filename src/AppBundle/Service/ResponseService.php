<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseService
{
    public function sendError($code, $msg, $callback = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'status' => false,
            'code' => $code,
            'data' => $msg
        ));

        if ($callback) {
            $response->setCallback($callback);
        }

        return $response;
    }

    public function sendSuccess($body, $callback = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'status' => true,
            'code' => 0,
            'data' => $body
        ));

        if ($callback) {
            $response->setCallback($callback);
        }

        return $response;
    }
}
