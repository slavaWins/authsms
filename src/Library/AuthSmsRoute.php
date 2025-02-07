<?php


namespace SlavaWins\AuthSms\Library;


use Illuminate\Support\Facades\Route;
use SlavaWins\AuthSms\Http\Controllers\CodeController;
use SlavaWins\AuthSms\Http\Controllers\LoginAuthSmsController;

class AuthSmsRoute
{

    public static function routes($loginRoute = "login")
    {
        Route::get('/auth', [LoginAuthSmsController::class, 'index'])->name($loginRoute);
        Route::post('/auth', [LoginAuthSmsController::class, 'phone'])->name('auth.phone.send');
        Route::post('/auth-email', [LoginAuthSmsController::class, 'email'])->name('auth.email.send');

        Route::post('/auth/code/{phonevertify}', [CodeController::class, 'code'])->name('auth.code.send');
        Route::post('/auth/password/{phonevertify}', [CodeController::class, 'password'])->name('auth.password.send');
    }

}
