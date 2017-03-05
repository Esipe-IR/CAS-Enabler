<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseService
{
    private function sendError($code, $msg, $callback)
    {
        $response = new JsonResponse();
        $response->setStatusCode(400);
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

    private function sendSuccess($body, $callback)
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
