<?php

namespace SlavaWins\AuthSms\Http\Controllers;

use App\Actions\AuthSms\CreateNewUser;
use App\Models\User;
use Barryvdh\Debugbar\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SlavaWins\AuthSms\Library\DeBruteService;
use SlavaWins\AuthSms\Models\PhoneVertify;

class CodeController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        PhoneVertify::where("id", ">", 0)->delete();
    }


    public function password(PhoneVertify $phonevertify, Request $request)
    {
        if (!env('AUTHSMS_USE_ONLY_PASSWORD', false)) {
            abort(404);
        }

        if ($phonevertify->created_at->diffInSeconds(Carbon::now()) > 60 * 3 || $phonevertify->is_closed) {
            $phonevertify->delete();
            return redirect()->back()->withErrors(['Код устарел, повторите авторизацию'])->withInput();
        }

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

        if (DeBruteService::IsBrutoforce("sms")) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsBrutoforce("sms") . ' сек.'])->withInput();
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

        if (env('AUTHSMS_USE_ONLY_PASSWORD', false)) {
            abort(404);
        }


        if ($phonevertify->created_at->diffInSeconds(Carbon::now()) > 60 * 3 || $phonevertify->is_closed) {
            return redirect()->back()->withErrors(['Код устарел, повторите авторизацию'])->withInput();
        }

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

        if(config("authsms.IsCheckIpEqCodeLogin")) {
            if ($phonevertify->ip <> $request->ip()) {
                return redirect()->back()->withErrors(['IP адреса с которых вы запросили код и ввели не совпадают'])->withInput();
            }
        }

        if (DeBruteService::IsBrutoforce("sms")) {
            return redirect()->back()->withErrors(['Превышено общие число попыток, подождите ' . DeBruteService::IsBrutoforce("sms") . ' сек.'])->withInput();
        }

        if (DeBruteService::IsGlobalBrutoforce($phonevertify->phone)) {
            return redirect()->back()->withErrors(['Этот аккаунт временно не доступен. Повторите вход через ' . DeBruteService::IsGlobalBrutoforce($phonevertify->phone) . ' сек.'])->withInput();
        }

        usleep(rand(0, 2800 ));

        if (!$phonevertify->IsCodeEqals( $data['code'])) {
            $phonevertify->try_count += 1;
            $phonevertify->save();

            if ($phonevertify->try_count > 3) {
               // $phonevertify->delete();
                return redirect()->back()->withErrors(['code' => 'Не осталось попыток.'])->withInput();
            }
            return redirect()->back()->withErrors(['code' => 'Не верный код попробуйте ещё раз. Осталось попыток: ' . (3 - $phonevertify->try_count)])
                ->withInput();
        }

        /** @var User $user */
        $user = null;

        if (config("authsms.AUTHSMS_USE_MAIL", false)) {
            $user = User::where("email", $phonevertify->phone)->first();
        } else {
            $user = User::where("phone", $phonevertify->phone)->first();
        }

        if (!$user) {
            $user = CreateNewUser::create();
            if (config("authsms.AUTHSMS_USE_MAIL", false)) {
                $user->email = $phonevertify->phone;
            } else {
                $user->phone = $phonevertify->phone;
            }
            $user->save();
        }

        $phonevertify->user_id = $user->id;
        $phonevertify->is_closed = true;
        $phonevertify->save();

        Auth::login($user);

        return redirect()->route("home");
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
