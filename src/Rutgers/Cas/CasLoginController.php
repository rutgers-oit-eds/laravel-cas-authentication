<?php

namespace Rutgers\Cas;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Routing\Controller;

use Rutgers\Cas\Events\Login;
use Rutgers\Cas\Facades\Cas;
use Rutgers\Cas\Exceptions\CasAuthorizationException;

class CasLoginController extends Controller
{

    public function login() {
        try {
            Auth::attempt();
        } catch (CasAuthorizationException $e) {
            Log::alert('Unauthorized user attempted to access the application', ['netID' => $e->netID()]);
            return response()->view('laravel-cas::casUserNotAuthorized', [], 401);
        }

        event(new Login(Auth::user()));

        //Redirect user to where they need to go, set in config by user
        return redirect()->intended(config('cas.cas_login_redirect_url'));
    }

    public function logout() {
        Auth::logout();

        return view('laravel-cas::loggedout');
    }

    public function cas_logout() {
        Cas::logout(config('cas.cas_logout_redirect_url'));
    }
}
