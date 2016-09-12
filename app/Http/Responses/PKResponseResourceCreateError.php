<?php
/**
 * Created by PhpStorm.
 * User: pavankataria
 * Date: 01/10/15
 * Time: 12:59
 */

namespace App\Http\Responses;
use Illuminate\Http\Response;


/**
 * Class PKResponseResourceCreateError
 * @package App\Http\Responses
 */
class PKResponseResourceCreateError extends PKResponse{

    function __construct()
    {
        $this->responseType = PKResponse::RESPONSE_ERROR;
        $this->message = 'The resource couldn\'t be created at this time. Try again.';
        $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}