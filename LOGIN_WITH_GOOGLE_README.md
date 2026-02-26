# Login with Google - Implementation Guide

This document explains how the "Login with Google" feature is implemented in this project using Laravel Socialite.

## 1. Prerequisites

Before using the Google Login feature, ensure that you have installed the **Laravel Socialite** package:

```bash
composer require laravel/socialite
```

Also, you must configure your Google application credentials in the `.env` file:

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

And in `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

## 2. Routes Update (`routes/web.php`)

The Google authentication routes must be placed **outside** the `admin` middleware group. Otherwise, users will be redirected to the admin panel.

```php
// social auth routes
Route::get('/auth/google', [App\Http\Controllers\SocialAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [App\Http\Controllers\SocialAuthController::class, 'handleGoogleCallback']);
```

## 3. Controller Implementation (`SocialAuthController.php`)

The `SocialAuthController` handles both the redirection to Google and the callback from Google.

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    // Redirects the user to the Google authentication page
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handles the callback from Google after successful authentication
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Check if a user with this email already exists
        $user = User::where('email', $googleUser->getEmail())->first();

        // If not, create a new user account
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(12345678) // Default random password
            ]);
        }

        // Log the user in using the 'user' guard
        Auth::guard('user')->login($user);
        
        // Redirect logic based on cart session
        if (count(session('cart', [])) > 0) {
            return redirect()->route('checkout')->with(['status' => true,  'message' => 'Login Successfully']);
        } else {
            return redirect()->route('index')->with(['status' => true,  'message' => 'Login Successfully']);
        }
    }
}
```

## 4. Frontend Usage (Blade views)

To use the Google login on your frontend (e.g., in `login.blade.php`), simply create an anchor (`<a>`) tag linking to the named route `google.login`:

```html
<a href="{{ route('google.login') }}" class="btn btn-danger">
    Login with Google
</a>
```

## Troubleshooting

- **Error: `Class "Laravel\Socialite\Facades\Socialite" not found`**
  Run `composer require laravel/socialite` and `php artisan optimize:clear`.

- **User redirected to Admin Panel login**
  Make sure your `Route::get('/auth/google', ...)` routes are **not** enclosed inside `Route::middleware('admin')->group(...)`.
