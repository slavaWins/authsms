<?php
    namespace SlavaWins\AuthSms\Http\Controllers;

    use App\Actions\AuthSms\CreateNewUser;
    use Barryvdh\Debugbar\Controllers\BaseController;
    use Carbon\Carbon;
    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use Illuminate\Foundation\Bus\DispatchesJobs;
    use Illuminate\Foundation\Validation\ValidatesRequests;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Http\Request;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use SlavaWins\AuthSms\Library\Formater;
    use SlavaWins\AuthSms\Models\PhoneVertify;

    class AuthSmsController extends BaseController
    {
        use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

        public function code(PhoneVertify $phonevertify, Request $request) {
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


            if ($phonevertify->code <> $data['code']) {
                return redirect()->back()->withErrors(['code' => 'Не верный код попробуйте ещё раз'])->withInput();
            }


            /** @var User $user */
            $user = User::where("phone", $phonevertify->phone)->first();

            if (!$user) {
                $user =  CreateNewUser::create();
                $user->phone = $phonevertify->phone;
                $user->save();
            }

            Auth::login($user);

            return redirect()->route("home");
        }

        public function phone(Request $request) {


            $data = $request->toArray();
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
                    'descr'  => 'Описание',
                    'phone'  => 'телефон',
                ]
            );


            $data = $validator->validate();

            $phone_draw =  Formater::formatPhoneNumber("7".$data['phone']);
            $phone = $data['phone'];

            /** @var PhoneVertify $phonevertify */
            $phonevertify = PhoneVertify::where("phone", $phone)->first();

            $antiBrutTime = 44;

            if ($phonevertify) {
                if ($phonevertify->try_count > 1) {
                    if (Carbon::now()->diffInSeconds($phonevertify->last_try) > $antiBrutTime) {
                        $phonevertify->try_count = 0;
                    }else {
                        return redirect()->back()->withErrors(['Привышено число попыток, подождите '.($antiBrutTime - Carbon::now()->diffInSeconds($phonevertify->last_try)).' сек.'])->withInput();
                    }
                }
            }else {
                $phonevertify = new PhoneVertify();
                $phonevertify->try_count = 0;
                $phonevertify->phone = $phone;
            }

            $phonevertify->try_count += 1;
            $phonevertify->last_try = Carbon::now();
            $phonevertify->code = rand(1000,9999);

            if(env('AUTHSMS_TEST_MODE', false)){
                $phonevertify->code = 1111;
            }

            $phonevertify->save();

            $tryId = $phonevertify->id;

            return view("authsms.phone-code", compact(['phone_draw', 'phone', 'tryId', 'phonevertify']));
        }

        
        public function index() {
            return view("authsms.phone");
        }

    }
