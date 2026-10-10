<?php
namespace App\Http\Controllers;

use Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
 
class GoogleController extends Controller

{
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }
         
    public function handleGoogleCallback()
    {
        try {

            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();

            if($finduser){
                Auth::login($finduser);
                return redirect()->intended('/');
            }else{

                $newUser = User::updateOrCreate(['email' => $user->email],[
                        'name' => $user->name,
                        'google_id'=> $user->id,
                        'password' => Hash::make('123456dummy'),
                        'avatar' => $user->avatar,
                        'is_email_verified' => 1,
                        'email_verified_at' => now(),
                    ]);

                Auth::login($newUser);
                return redirect()->intended('/');
            }     

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

}