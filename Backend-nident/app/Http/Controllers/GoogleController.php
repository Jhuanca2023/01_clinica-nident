<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
 
class GoogleController extends Controller

{
    public function redirectToGoogle() {
        //=========== ACTIVAR PARA PRODUCCIÓN ===========
        return Socialite::driver('google')->redirect();
        //=========== ACTIVAR PARA PREUBAS ===========
        // return Socialite::driver('google')
        //     ->with(['prompt' => 'select_account'])
        //     ->redirect();
    }
         
    public function handleGoogleCallback()
    {
        try {

            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();

            if($finduser){
                if(!$finduser->is_email_verified) {
                    return redirect()->route('login')->withErrors([
                        'email' => 'Necesita confirmar su cuenta. Por favor, revise su correo electrónico.',
                    ]);
                }
                Session::put('login_provider', 'google');
                Auth::login($finduser);   
                return redirect()->intended('/');
            }else{

                $newUser = User::updateOrCreate(['email' => $user->email],[
                        'name' => $user->name,
                        'google_id'=> $user->id,
                        'password' => Hash::make('3$E64xw'),
                        'avatar' => $user->avatar,
                        'is_email_verified' => 1,
                        'email_verified_at' => now(),
                    ]);

                Session::put('login_provider', 'google');
                Auth::login($newUser);
                return redirect()->intended('/');
            }     

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

}