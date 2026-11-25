<?php

namespace SlavaWins\AuthSms\Http\Controllers;

use App\Actions\AuthSms\SendSms;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberUtil;
use SlavaWins\AuthSms\Library\AuthSmsConfig;
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

    private static function InputLogin(Request $request): string
    {
        $login = $request->input('login');
        return is_string($login) ? $login : '';
    }

    public function phone(Request $request)
    {
        if (AuthSmsConfig::UseMail()) {
            abort(404);
        }

        $data = ['phone' => str_replace([' ', '(', ')', '-'], '', self::InputLogin($request))];

        $validator = Validator::make(
            $data,
            [
                'phone' => 'required|string|min:10|max:10|regex:/^\d{10}$/',
            ],
            [
            ],
            [
                'phone' => 'телефон',
            ]
        );

        $data = $validator->validate();

        $phone_draw = Formater::formatPhoneNumber("7" . $data['phone']);
        $phone = config("authsms.PreappenPhoneCode", "") . $data['phone'];

        if (!$this->validatePhoneNumber($phone)) {
            return redirect()->back()->withErrors(['Некорректный номер телефона'])->withInput();
        }

        return $this->StartAuth($request, $phone, $phone_draw, 10);
    }


    public function email(Request $request)
    {
        if (!AuthSmsConfig::UseMail()) {
            abort(404);
        }

        $data = ['email' => mb_strtolower(str_replace(' ', '', self::InputLogin($request)))];

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

        return $this->StartAuth($request, $data['email'], $data['email'], 5);
    }


    /**
     * Общая часть для телефона и почты: лимиты, проверка аккаунта, создание попытки и отправка кода.
     */
    private function StartAuth(Request $request, string $login, string $phone_draw, int $checkLoginsLimit)
    {
        $isMail = AuthSmsConfig::UseMail();
        $field = AuthSmsConfig::LoginField();

        $onlyLogin = AuthSmsConfig::OnlyLogin();
        if ($onlyLogin !== null && mb_strtolower($login) !== $onlyLogin) {
            return redirect()->back()->withErrors(['Сайт находится в разработке, и система авторизации отключена. Извините.'])->withInput();
        }

        $limitsOn = !AuthSmsConfig::IsLimitsDisabled();

        if ($limitsOn) {
            if ($sec = DeBruteService::IsGlobalBrutoforce($login)) {
                return redirect()->back()->withErrors(['Аккаунт временно заблокирован, подождите ' . $sec . ' сек.'])->withInput();
            }

            if ($sec = DeBruteService::IsBrutoforce("sms")) {
                return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . $sec . ' сек.'])->withInput();
            }
        }

        if (!AuthSmsConfig::AutoRegistration()) {
            if ($limitsOn) {
                if ($sec = DeBruteService::IsBrutoforceCustom("checkLogins", $checkLoginsLimit)) {
                    return redirect()->back()->withErrors(['Превышен лимит входа в разные аккаунты, подождите ' . $sec . ' сек.'])->withInput();
                }
            }

            if (!User::where($field, $login)->exists()) {
                return redirect()->back()->withErrors(['Не найден аккаунт'])->withInput();
            }
        }

        /** @var User|null $user */
        $user = User::where($field, $login)->first();

        $passwordMode = AuthSmsConfig::UsePasswordOnly();

        // Регистрация (или у пользователя ещё нет пароля) — логин нужно подтвердить кодом.
        $isRegister = !$user || ($passwordMode && empty($user->password));

        // В режиме пароля код отправляем только при регистрации — обычный вход идёт по паролю
        $needCode = !$passwordMode || $isRegister;

        $code = AuthSmsConfig::IsTestMode() ? "1111" : PhoneVertify::GetRandomCode();
        $phonevertify = PhoneVertify::MakeTryByPhone($login, $request->ip(), $code);

        if ($needCode && !AuthSmsConfig::IsTestMode()) {
            try {
                $sended = $isMail
                    ? SendEmail::send($login, $code)
                    : SendSms::send($login, SendSms::messageAuth($code));
            } catch (\Throwable $e) {
                Log::error('authsms: ошибка отправки кода: ' . $e->getMessage());
                $sended = false;
            }
            $phonevertify->is_sended_on_phone = (bool)$sended;
            $phonevertify->save();
        }

        $phone = $login;
        $tryId = $phonevertify->id;

        $compact = compact(['phone_draw', 'phone', 'tryId', 'phonevertify', 'isRegister', 'needCode']);

        if ($passwordMode) {
            return view("authsms::authsms.password", $compact);
        }

        return view("authsms::authsms.phone-code", $compact);
    }


    public function index()
    {
        if (AuthSmsConfig::UseMail()) {
            return view("authsms::authsms.email");
        }

        return view("authsms::authsms.phone");
    }

}
