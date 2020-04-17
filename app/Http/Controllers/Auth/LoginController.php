<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Socialite;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider($provider)
    {
       return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
      
      try {

        $user = Socialite::driver($provider)->stateless()->user();
        $authUser = $this->checkUser($user, $provider);
        Auth::login($authUser, true);
        return redirect($this->redirectTo);

      }catch (Exception $e) {
        return redirect('/login');
      }
      
    }

    public function checkUser($providerUser, $provider)
    {
      
      $account = User::where('provider_name', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();
      if ($account) {
          return  $account;
      } else {
           $user = User::where('email', $providerUser->getEmail())
          ->first();
          if (! $user) {
              $user = User::create([
                'email' => $providerUser->getEmail(),
                'name'  => $providerUser->getName(),
                'provider_id'   => $providerUser->getId(),
                'provider_name' => $provider,
              ]);
          }
          return $user;
      }
    }
}
