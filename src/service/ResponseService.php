<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResponseService
{
    /**
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param integer $statusCode the status code
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param array $data
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function error($data, $headers = [])
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param array $data
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function success($data, $headers = [])
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

}