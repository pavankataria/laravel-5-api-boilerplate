<?php
/**
 * Created by PhpStorm.
 * User: pavankataria
 * Date: 09/10/15
 * Time: 15:15
 */

namespace App\Http\Oauth;


use App\Http\Responses\PKResponseLoginInvalidCredentialsException;
use App\Http\Responses\PKResponseResourceDeleteError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\OAuth2\Server\Exception\InvalidCredentialsException;
use League\OAuth2\Server\Exception\ServerErrorException;

/**
 * Class Proxy
 * @package App\Http\Oauth
 */
class Proxy {
    /**
     * @param $credentials
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function attemptLogin($credentials)
    {
        $grantType = 'password';
        return $this->proxy($grantType, $credentials);
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function attemptRefresh()
    {
        $crypt  = App::make(Encrypter::class);
//        $request = App::make(Request::class);

        return $this->proxy('refresh_token', [
            'refresh_token' => $crypt->decrypt(\Illuminate\Support\Facades\Request::cookie('refreshToken'))
        ]);
    }

    /**
     * @param $grantType
     * @param array $data
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    private function proxy($grantType, array $data = [])
    {
        try {
            $data = array_merge([
                'client_id'     => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'grant_type'    => $grantType
            ], $data);

            $client = new Client();
            $guzzleResponse = $client->post(route('oauth.accesstoken'), [
                'form_params' => $data
            ]);
        }
        catch(BadResponseException $e) {
            $guzzleResponse = $e->getResponse();
            dd($guzzleResponse);

            if($e instanceof InvalidCredentialsException){
                return PKResponseLoginInvalidCredentialsException;
            }
        }
        catch(\Exception $e){
            return $e->getMessage();
        }

        $response = json_decode($guzzleResponse->getBody());
        if (property_exists($response, "access_token")) {
            $cookie = app()->make('cookie');
            $crypt  = App::make(Encrypter::class);


            $encryptedToken = $crypt->encrypt($response->refresh_token);

            // Set the refresh token as an encrypted HttpOnly cookie
            $cookie->queue('refreshToken',
                $encryptedToken,
                604800, // expiration, should be moved to a config file
                null,
                null,
                false,
                true // HttpOnly
            );

            $response = [
                'accessToken'            => $response->access_token,
                'accessTokenExpiration'  => $response->expires_in
            ];

        }

        $response = response()->json($response);
        $response->setStatusCode($guzzleResponse->getStatusCode());

        $headers = $guzzleResponse->getHeaders();
        foreach($headers as $headerType => $headerValue) {
            $response->header($headerType, $headerValue);
        }

        return $response;
    }
}