<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;


class SocialAuthController extends Controller
{
    
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(12345678)
            ]);
        }

        // Log in using the 'user' guard
        Auth::guard('user')->login($user);
        
        if (count(session('cart', [])) > 0) {
                return redirect()->route('checkout')->with(['status' => true,  'message' => 'Login Successfully']);
            } else {

                return redirect()->route('index')->with(['status' => true,  'message' => 'Login Successfully']);
            }
    }
}
