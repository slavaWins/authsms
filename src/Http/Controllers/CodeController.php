<?php

namespace SlavaWins\AuthSms\Http\Controllers;

use App\Actions\AuthSms\CreateNewUser;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SlavaWins\AuthSms\Library\AuthSmsConfig;
use SlavaWins\AuthSms\Library\DeBruteService;
use SlavaWins\AuthSms\Models\PhoneVertify;

class CodeController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** Максимум попыток ввода на одну выданную попытку входа (реально доступно MAX_TRY - 1) */
    private const MAX_TRY = 3;


    /**
     * Общие проверки перед вводом кода/пароля: IP, лимит по IP, лимит по логину.
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function CheckLimits(PhoneVertify $phonevertify, Request $request)
    {
        if (AuthSmsConfig::IsLimitsDisabled()) return null;

        if (config("authsms.IsCheckIpEqCodeLogin", true)) {
            if ($phonevertify->ip !== $request->ip()) {
                return redirect()->back()->withErrors(['IP адреса с которых вы запросили код и ввели не совпадают'])->withInput();
            }
        }

        if ($sec = DeBruteService::IsBrutoforce("sms")) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . $sec . ' сек.'])->withInput();
        }

        if ($sec = DeBruteService::IsGlobalBrutoforce($phonevertify->phone)) {
            return redirect()->back()->withErrors(['Этот аккаунт временно не доступен. Повторите вход через ' . $sec . ' сек.'])->withInput();
        }

        return null;
    }

    /**
     * Списывает попытку. Случайная задержка — против timing-атак.
     * @return bool false — попытки закончились
     */
    private function SpendTry(PhoneVertify $phonevertify): bool
    {
        usleep(random_int(0, 2800));

        $phonevertify->try_count += 1;
        $phonevertify->last_try = now();
        $phonevertify->save();

        return !($phonevertify->try_count >= self::MAX_TRY || $phonevertify->is_closed);
    }

    private function FindUser(PhoneVertify $phonevertify): ?User
    {
        return User::where(AuthSmsConfig::LoginField(), $phonevertify->phone)->first();
    }

    private function CreateUser(PhoneVertify $phonevertify): User
    {
        $user = CreateNewUser::create();
        $user->{AuthSmsConfig::LoginField()} = $phonevertify->phone;
        $user->save();
        return $user;
    }

    private function LoginAndClose(PhoneVertify $phonevertify, User $user)
    {
        $phonevertify->user_id = $user->id;
        $phonevertify->is_closed = true;
        $phonevertify->save();

        Auth::login($user);

        return redirect()->route("home");
    }


    /**
     * Режим AUTHSMS_USE_ONLY_PASSWORD.
     *  - есть пользователь с паролем → проверяем пароль;
     *  - регистрация / пароля ещё нет → нужен код из SMS/письма + новый пароль.
     */
    public function password(PhoneVertify $phonevertify, Request $request)
    {
        if (!AuthSmsConfig::UsePasswordOnly()) {
            abort(404);
        }

        if ($phonevertify->IsExpired()) {
            return redirect()->back()->withErrors(['Код устарел, повторите авторизацию'])->withInput();
        }

        $user = $this->FindUser($phonevertify);
        $isRegister = !$user || empty($user->password);

        if (!$user && !AuthSmsConfig::AutoRegistration()) {
            return redirect()->back()->withErrors(['Не найден аккаунт'])->withInput();
        }

        $rules = $isRegister
            ? [
                'code' => 'required|string|min:4|max:4|regex:/^\d{4}$/',
                'password' => 'required|string|min:8|max:72',
            ]
            : [
                'password' => 'required|string|min:1|max:72',
            ];

        $data = Validator::make($request->only(['code', 'password']), $rules, [], [
            'code' => 'код',
            'password' => 'пароль',
        ])->validate();

        if ($error = $this->CheckLimits($phonevertify, $request)) {
            return $error;
        }

        if (!$this->SpendTry($phonevertify)) {
            return redirect()->back()->withErrors(['code' => 'Не осталось попыток.'])->withInput();
        }

        if ($isRegister) {
            if (!$phonevertify->IsCodeEqals($data['code'])) {
                return redirect()->back()->withErrors(['code' => 'Не верный код попробуйте ещё раз'])->withInput();
            }
        } else {
            if (!Hash::check($data['password'], $user->password)) {
                return redirect()->back()->withErrors(['password' => 'Не правильный пароль'])->withInput();
            }
        }

        if (!$user) {
            $user = $this->CreateUser($phonevertify);
        }

        if ($isRegister) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        return $this->LoginAndClose($phonevertify, $user);
    }

    public function code(PhoneVertify $phonevertify, Request $request)
    {
        if (AuthSmsConfig::UsePasswordOnly()) {
            abort(404);
        }

        if ($phonevertify->IsExpired()) {
            return redirect()->back()->withErrors(['Код устарел, повторите авторизацию'])->withInput();
        }

        $data = Validator::make(
            $request->only(['code']),
            [
                'code' => 'required|string|min:4|max:4|regex:/^\d{4}$/',
            ],
            [
            ],
            [
                'code' => 'код',
            ]
        )->validate();

        if ($error = $this->CheckLimits($phonevertify, $request)) {
            return $error;
        }

        if (!$this->SpendTry($phonevertify)) {
            return redirect()->back()->withErrors(['code' => 'Не осталось попыток.'])->withInput();
        }

        if (!$phonevertify->IsCodeEqals($data['code'])) {
            return redirect()->back()->withErrors(['code' => 'Не верный код попробуйте ещё раз'])->withInput();
        }

        $user = $this->FindUser($phonevertify);

        if (!$user) {
            if (!AuthSmsConfig::AutoRegistration()) {
                return redirect()->back()->withErrors(['Не найден аккаунт'])->withInput();
            }
            $user = $this->CreateUser($phonevertify);
        }

        return $this->LoginAndClose($phonevertify, $user);
    }

}
