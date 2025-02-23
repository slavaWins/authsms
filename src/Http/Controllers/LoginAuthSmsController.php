<?php

namespace SlavaWins\AuthSms\Http\Controllers;

use App\Actions\AuthSms\SendSms;
use App\Models\User;
use Barryvdh\Debugbar\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberUtil;
use SlavaWins\AuthSms\Library\DeBruteService;
use SlavaWins\AuthSms\Library\Formater;
use SlavaWins\AuthSms\Library\SendEmail;
use SlavaWins\AuthSms\Models\PhoneVertify;

class LoginAuthSmsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function validatePhoneNumber($phone)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($phone, "RU"); // "RU" - код страны по умолчанию
            return $phoneUtil->isValidNumber($numberProto);
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }
    }

    public function phone(Request $request)
    {
        if (config("authsms.AUTHSMS_USE_MAIL")) {
            abort(404);
        }

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
                'phone' => 'телефон',
            ]
        );


        $data = $validator->validate();

        $phone_draw = Formater::formatPhoneNumber("7" . $data['phone']);
        $phone = $data['phone'];

        if (!$this->validatePhoneNumber($phone)) {
            return redirect()->back()->withErrors(['Не корректный номер телефона'])->withInput();
        }

        if (config("authsms.AUTHSMS_USE_MAIL", false)) {
            return redirect()->back()->withErrors(['Не поддерживаемый способ авторизации'])->withInput();
        }

        if (env('AUTHSMS_USE_ONLY_PHONE', false)) {
            if ($phone <> env('AUTHSMS_USE_ONLY_PHONE', false)) {
                return redirect()->back()->withErrors(['Сайт находится в разработке, и система авторизации отключена. Извините.'])->withInput();
            }
        }


        if (DeBruteService::IsGlobalBrutoforceAll()) {
            return redirect()->back()->withErrors(['Сервис временно недоступен, повторите запрос через ' . DeBruteService::IsGlobalBrutoforceAll("sms") . ' сек'])->withInput();
        }


        if (DeBruteService::IsGlobalBrutoforce($phone)) {
            return redirect()->back()->withErrors(['Аккаунт временно заблокирован, подождите ' . DeBruteService::IsGlobalBrutoforce($phone) . ' сек.'])->withInput();
        }


        if (DeBruteService::IsBrutoforce("sms")) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsBrutoforce("sms") . ' сек.'])->withInput();
        }


        if (!config("authsms.AUTHSMS_AUTO_REGISTRATION", false)) {


            if (DeBruteService::IsBrutoforceCustom("checkLogins", 10)) {
                return redirect()->back()->withErrors(['Превышен лимит входа в разные аккаунты ' . DeBruteService::IsBrutoforceCustom("checkLogins") . ' сек.'])->withInput();
            }


            if (!User::where("phone", $phone)->exists()) {
                return redirect()->back()->withErrors(['Не найден аккаунт'])->withInput();
            }
        }

        $code = PhoneVertify::GetRandomCode();
        $phonevertify = PhoneVertify::MakeTryByPhone($phone, $request->ip(), $code);


        $antiBrutTime = 44;
        if ($phonevertify->try_count > 2) {
            if (Carbon::now()->diffInSeconds($phonevertify->last_try) > $antiBrutTime) {
                $phonevertify->try_count = 0;
            } else {
                return redirect()->back()->withErrors(['Превышено число попыток, подождите ' . ($antiBrutTime - Carbon::now()->diffInSeconds($phonevertify->last_try)) . ' сек.'])->withInput();
            }
        }



        if (config('authsms.AUTHSMS_TEST_MODE', false)) {
            $phonevertify->SetCode("1111");
        } else {
            SendSms::send($phone, SendSms::messageAuth($code));
        }

        $phonevertify->save();

        $tryId = $phonevertify->id;

        $isRegister = User::where("phone", $phone)->first() == null;


        $compact = compact(['phone_draw', 'phone', 'tryId', 'phonevertify', 'isRegister']);

        if (env('AUTHSMS_USE_ONLY_PASSWORD', false)) {
            return view("authsms::authsms.password", $compact);
        } else {
            return view("authsms::authsms.phone-code", $compact);
        }

    }


    public function email(Request $request)
    {

        if (!config("authsms.AUTHSMS_USE_MAIL")) {
            abort(404);
        }

        $data = $request->toArray();

        $data['email'] = $data['login'] ?? "";
        $data['email'] = str_replace(' ', '', $data['email']);


        $validator = Validator::make(
            $data,
            [
                'email' => 'required|email|min:3|max:50',
            ],
            [
            ],
            [
                'email' => 'почта',
            ]
        );


        $data = $validator->validate();

        $phone_draw = $data['email'];
        $phone = $data['email'];


        if (env('AUTHSMS_USE_ONLY_PHONE', false)) {
            if ($phone <> env('AUTHSMS_USE_ONLY_PHONE', false)) {
                return redirect()->back()->withErrors(['Сайт находится в разработке, и система авторизации отключена. Извините.'])->withInput();
            }
        }


        if (DeBruteService::IsGlobalBrutoforceAll()) {
            return redirect()->back()->withErrors(['Сервис временно недоступен, повторите запрос через ' . DeBruteService::IsGlobalBrutoforceAll() . ' сек'])->withInput();
        }

        if (DeBruteService::IsGlobalBrutoforce($phone)) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsGlobalBrutoforce($phone) . ' сек.'])->withInput();
        }

        if (DeBruteService::IsBrutoforce("sms")) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsBrutoforce("sms") . ' сек.'])->withInput();
        }


        if (!config("authsms.AUTHSMS_AUTO_REGISTRATION", false)) {

            if (DeBruteService::IsBrutoforceCustom("checkLogins")) {
                return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsBrutoforceCustom("checkLogins") . ' сек.'])->withInput();
            }

            if (!User::where("email", $phone)->exists()) {
                return redirect()->back()->withErrors(['Не найден аккаунт'])->withInput();
            }
        }


        $code = PhoneVertify::GetRandomCode();
        $phonevertify = PhoneVertify::MakeTryByPhone($phone, $request->ip(), $code);


        $antiBrutTime = 44;
        if ($phonevertify->try_count > 2) {
            if (Carbon::now()->diffInSeconds($phonevertify->last_try) > $antiBrutTime) {
                $phonevertify->try_count = 0;
            } else {
                return redirect()->back()->withErrors(['Превышено число попыток, подождите ' . ($antiBrutTime - Carbon::now()->diffInSeconds($phonevertify->last_try)) . ' сек.'])->withInput();
            }

        }


        if (config('authsms.AUTHSMS_TEST_MODE',false)) {
            $phonevertify->SetCode("1111");
        } else {
            SendEmail::send($phone, $code);
        }

        $phonevertify->save();

        $tryId = $phonevertify->id;

        $isRegister = User::where("phone", $phone)->first() == null;


        $compact = compact(['phone_draw', 'phone', 'tryId', 'phonevertify', 'isRegister']);

        if (env('AUTHSMS_USE_ONLY_PASSWORD', false)) {
            return view("authsms::authsms.password", $compact);
        } else {
            return view("authsms::authsms.phone-code", $compact);
        }

    }


    public function index()
    {
        if (env("AUTHSMS_USE_MAIL")) {
            return view("authsms::authsms.email");
        } else {
            return view("authsms::authsms.phone");
        }
    }

}
