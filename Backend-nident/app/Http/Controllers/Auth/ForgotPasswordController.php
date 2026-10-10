<?php

namespace App\Http\Controllers\Auth;
 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 
use App\Models\User; 
use Mail; 
use Hash;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{

      public function showForgetPasswordForm()
      {
         return view('auth.forgetPassword');
      }

      public function submitForgetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
          ]);

          $token = Str::random(64);

          DB::table('password_resets')->insert([
              'email' => $request->email, 
              'token' => $token, 
              'created_at' => Carbon::now()
            ]);

          Mail::send('email.forgetPassword', ['token' => $token,'email'=>$request->email], function($message) use($request){
              $message->to($request->email);
              $message->subject('Restablece tu contraseña — Valento');
          }); 


        //   (new MailMessage)
        // ->subject('Reset Password Notification')
        // ->line('You are receiving this email because we received a password reset request for your account.')
        // ->action('Reset Password', $url)
        // ->line('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])
        // ->line('If you did not request a password reset, no further action is required.');
        
          return back()->with('message', '¡Le hemos enviado por correo electrónico su enlace para restablecer su contraseña!');

      }

      public function showResetPasswordForm($token) { 
         return view('auth.forgetPasswordLink', ['token' => $token]);
      }


      public function submitResetPasswordForm(Request $request)

      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]); 

          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first(); 
                            //  dd( $updatePassword);
          if(!$updatePassword){
              return back()->withInput()->with('error', 'Invalid token!');
          }
  
          User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
          DB::table('password_resets')->where(['email'=> $request->email])->delete();

          return redirect('/login')->with('messageOlvidoContrasena', 'Su contraseña ha sido cambiada!');
      }

}

/*
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
}
*/

