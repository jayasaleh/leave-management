<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
class GoogleController extends Controller
{
    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }
    public function handleGoogleCallback(){
        try {
            $googleUser = Socialite::driver('google')->user();
            //Cek apakah user sudah terdaftar
            $user= User::where('email',$googleUser->email)->first();
            if($user){
                Auth::login($user);
                return redirect()->intended('/dashboard');
            }else{
                //Cek apakah email sudah terdaftar (registrasi manual)
                $existingUser=User::where('email',$googleUser->email)->first();
                if($existingUser){
                    $existingUser->update([
                        'google_id'=>$googleUser->id,
                        'name'=>$googleUser->name,
                    ]);
                    Auth::login($existingUser);
                } else{
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'password' => bcrypt(Str::random(24)),
                        'email_verified_at' => now(),
                    ]);
                    Auth::login($user);
                }
                return redirect()->intended('/dashboard');
            }
        } catch(\Exception $e){
            return redirect('/login')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }
}
