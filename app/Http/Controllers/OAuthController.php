<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use League\OAuth2\Server\Exception\InvalidCredentialsException;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;

use App\Http\Oauth\Proxy;
use App\Http\Requests;

/**
 * Class OAuthController
 * @package App\Http\Controllers
 */
class OAuthController extends ApiController
{
    /**
     *
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiManager->setStatusCode(422)->respond($validator->errors());
        }

        $credentials = array_only($request->all(), ['username', 'password']);
        return App::make(Proxy::class)->attemptLogin($credentials);

//    $credentials = array_merge([
//        'client_id'     => env('OAUTH_CLIENT_ID'),//$config->get('secrets.client_id'),
//        'client_secret' => env('OAUTH_CLIENT_SECRET'),//$config->get('secrets.client_secret'),
//        'grant_type'    => 'password'
//    ], $credentials);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function accessToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grant_type' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
//            'username' => 'required',
//            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiManager->setStatusCode(422)->respond($validator->errors());
        }
        try{
            return $this->apiManager->respond(Authorizer::issueAccessToken());
        }
        catch(InvalidCredentialsException $e){
            return $this->apiManager->respondWithErrorCodeAndMessage(Response::HTTP_UNAUTHORIZED,$e->getMessage());
        }
//        return Response::json($accessTokenArray);
//        $username = Request::input('username');
//        $password = Request::input('password');
//        return Response::json(Auth::attempt(['username'=> $username, 'password' => $password]));
//    $accessTokenArray = Authorizer::issueAccessToken();
//    return Response::json($accessTokenArray);
    }
    public function refreshToken(Request $request)
    {
        return App::make(Proxy::class)->attemptRefresh();
    }
}
