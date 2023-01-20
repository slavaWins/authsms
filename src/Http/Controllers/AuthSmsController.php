<?php

namespace SlavaWins\AuthSms\Http\Controllers;

use App\Actions\AuthSms\CreateNewUser;
use App\Actions\AuthSms\SendSms;
use App\Models\ResponseApi;
use Barryvdh\Debugbar\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\Concerns\Has;
use SlavaWins\AuthSms\Library\Formater;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Illuminate\Support\Facades\Hash;

class AuthSmsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function password(PhoneVertify $phonevertify, Request $request)
    {
        $data = $request->toArray();

        $validator = Validator::make(
            $data,
            [
                'code' => 'required|string|min:6|max:6',
            ],
            [
            ],
            [
                'code' => 'пароль',
            ]
        );

        $data = $validator->validate();

        if (self::AntiBrut("sms")) {
            return redirect()->back()->withErrors(['Привышено общие число попыток, подождите ' . $seconds . ' сек.'])->withInput();
        }

        if ($phonevertify->ip <> $_SERVER['REMOTE_ADDR']) {
            return redirect()->back()->withErrors(['Ошибка ip адресса'])->withInput();
        }
        /** @var User $user */
        $user = User::where("phone", $phonevertify->phone)->first();

        if ($user) {
            if (!Hash::check($data['code'], $user->password)) {
                return redirect()->back()->withErrors(['code' => 'Не правильный пароль'])->withInput();
            }
        }

        if (!$user) {
            $user = CreateNewUser::create();
            $user->phone = $phonevertify->phone;
            $user->password = Hash::make($data['code']);
            $user->save();
        }

        $phonevertify->user_id = $user->id;
        $phonevertify->save();

        Auth::login($user);

        return redirect()->route("home");
    }

    public function code(PhoneVertify $phonevertify, Request $request)
    {
        $data = $request->toArray();

        $validator = Validator::make(
            $data,
            [
                'code' => 'required|string|min:4|max:4|regex:/^\d+(\.\d{1,2})?$/',
            ],
            [
            ],
            [
                'code' => 'код',
            ]
        );

        $data = $validator->validate();

        if ($phonevertify->ip <> $_SERVER['REMOTE_ADDR']) {
            return redirect()->back()->withErrors(['Ошибка ip адресса'])->withInput();
        }

        if (self::AntiBrut("sms")) {
            return redirect()->back()->withErrors(['Привышено общие число попыток, подождите ' . $seconds . ' сек.'])->withInput();
        }

        if ($phonevertify->code <> $data['code']) {
            return redirect()->back()->withErrors(['code' => 'Не верный код попробуйте ещё раз'])->withInput();
        }

        /** @var User $user */
        $user = User::where("phone", $phonevertify->phone)->first();

        if (!$user) {
            $user = CreateNewUser::create();
            $user->phone = $phonevertify->phone;
            $user->save();
        }

        $phonevertify->user_id = $user->id;
        $phonevertify->save();

        Auth::login($user);

        return redirect()->route("home");
    }

    public static function AntiBrut($ind)
    {
        if (env('AUTHSMS_TEST_AttemptsMaxByIp', 0) > 0) {
            $limitKey = $ind . $_SERVER['REMOTE_ADDR'];
            if (RateLimiter::tooManyAttempts($limitKey, env('AUTHSMS_TEST_AttemptsMaxByIp'))) {
                $seconds = RateLimiter::availableIn($limitKey);
                return true;
            }
        }
        return false;
    }

    public function phone(Request $request)
    {

        $data = $request->toArray();

        $data['phone'] = $data['login'] ?? "";
        $data['phone'] = str_replace(' ', '', $data['phone']);
        $data['phone'] = str_replace('(', '', $data['phone']);
        $data['phone'] = str_replace(')', '', $data['phone']);
        $data['phone'] = str_replace('-', '', $data['phone']);


        $validator = Validator::make(
            $data,
            [
                'phone' => 'required|string|min:10|max:10|regex:/^\d+(\.\d{1,2})?$/',
            ],
            [
            ],
            [
                'budget' => 'Бюджет',
                'descr' => 'Описание',
                'phone' => 'телефон',
            ]
        );


        $data = $validator->validate();

        $phone_draw = Formater::formatPhoneNumber("7" . $data['phone']);
        $phone = $data['phone'];

        if (env('AUTHSMS_USE_ONLY_PHONE', false)) {
            if ($phone <> env('AUTHSMS_USE_ONLY_PHONE', false)) {
                return redirect()->back()->withErrors(['Сайт находится в разработке, и система авторизации отключена. Извините.'])->withInput();
            }
        }
        /** @var PhoneVertify $phonevertify */
        $phonevertify = PhoneVertify::where("phone", $phone)->where("ip", $_SERVER['REMOTE_ADDR'])->first();

        if (self::AntiBrut("sms")) {
            return redirect()->back()->withErrors(['Привышено общие число попыток, подождите ' . $seconds . ' сек.'])->withInput();
        }


        $antiBrutTime = 44;
        if ($phonevertify) {
            if ($phonevertify->try_count > 1) {
                if (Carbon::now()->diffInSeconds($phonevertify->last_try) > $antiBrutTime) {
                    $phonevertify->try_count = 0;
                } else {
                    return redirect()->back()->withErrors(['Привышено число попыток, подождите ' . ($antiBrutTime - Carbon::now()->diffInSeconds($phonevertify->last_try)) . ' сек.'])->withInput();
                }
            }
        } else {
            $phonevertify = new PhoneVertify();
            $phonevertify->try_count = 0;
            $phonevertify->phone = $phone;
            $phonevertify->ip = $_SERVER['REMOTE_ADDR'];
        }

        $phonevertify->try_count += 1;
        $phonevertify->last_try = Carbon::now();
        $phonevertify->code = rand(1000, 9999);

        if (env('AUTHSMS_TEST_MODE', false)) {
            $phonevertify->code = 1111;
        } else {
            SendSms::send($phone, SendSms::messageAuth($phonevertify->code));
        }

        $phonevertify->save();

        $tryId = $phonevertify->id;

        $isRegister = User::where("phone", $phone)->first() == null;


        $compact = compact(['phone_draw', 'phone', 'tryId', 'phonevertify', 'isRegister']);

        if (env('AUTHSMS_USE_ONLY_PASSWORD', false)) {
            return view("authsms.password", $compact);
        } else {
            return view("authsms.phone-code", $compact);
        }

    }


    public function index()
    {
        return view("authsms.phone");
    }

}
