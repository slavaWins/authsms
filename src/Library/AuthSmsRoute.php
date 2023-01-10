<?php


    namespace SlavaWins\AuthSms\Library;


    use Illuminate\Support\Facades\Route;
    use SlavaWins\AuthSms\Http\Controllers\AuthSmsController;

    class AuthSmsRoute
    {

        public static function routes() {
            Route::get('/auth', [AuthSmsController::class, 'index'])->name('login');
          //  Route::get('/register', [AuthSmsController::class, 'index'])->name('register');
            Route::post('/auth', [AuthSmsController::class, 'phone'])->name('auth.phone.send');
            Route::post('/auth/code/{phonevertify}', [AuthSmsController::class, 'code'])->name('auth.code.send');
        }

    }
