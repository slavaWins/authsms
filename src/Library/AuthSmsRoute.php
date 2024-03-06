<?php


namespace SlavaWins\AuthSms\Library;


use Illuminate\Support\Facades\Route;
use SlavaWins\AuthSms\Http\Controllers\AuthSmsController;

class AuthSmsRoute
{

    public static function routes()
    {
        Route::get('/auth', [AuthSmsController::class, 'index'])->name('login');
        Route::post('/auth', [AuthSmsController::class, 'phone'])->name('auth.phone.send');
        Route::post('/auth-email', [AuthSmsController::class, 'email'])->name('auth.email.send');
        Route::post('/auth/code/{phonevertify}', [AuthSmsController::class, 'code'])->name('auth.code.send');
        Route::post('/auth/password/{phonevertify}', [AuthSmsController::class, 'password'])->name('auth.password.send');
    }

}
