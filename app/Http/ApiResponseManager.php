<?php
/**
 * Created by PhpStorm.
 * User: pavankataria
 * Date: 29/09/15
 * Time: 10:59
 */

namespace App\Http;

use Response;

class ApiResponseManager {

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = []){
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param $message
     * @return mixed
     */
    public function respondWithError($message){
        return $this->respond([
            'response' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }
    public function respondWithSuccess($message){
        return $this->respond([
            'success' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }
    public function respondWithMessage($message){
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }
    public function respondWithErrorCodeAndMessage($code, $message)
    {
        $this->setStatusCode($code);
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }
    public function respondBadRequest($message = 'The server cannot process the response, bad request')
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }
    /**
     * @param string $message
     * @return mixed
     */
    public function respondServerError($message = 'There was an internal server error!')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }
} 