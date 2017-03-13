<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseService
{
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
    
    public function sendError($code, $error, $callback = null)
    {
        return $this->send(false, $code, null, $error, $callback);
    }

    public function sendSuccess($data, $callback = null)
    {
        return $this->send(true, 0, $data, null, $callback);
    }
}
