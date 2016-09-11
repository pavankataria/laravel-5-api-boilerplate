<?php
/**
 * Created by PhpStorm.
 * User: pavankataria
 * Date: 09/10/15
 * Time: 05:36
 */

namespace App\Http\Oauth;
use Illuminate\Support\Facades\Auth;

class Verifier {
    public function verify($username, $password)
    {
        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {
            return Auth::user()->id;
        }

        return false;
    }
} 