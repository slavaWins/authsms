@php
    use SlavaWins\Formbuilder\Library\FElement;

   /*** @var $user \app\Models\User */
@endphp


<div class="col-4">
    <div class="card mb-4">
        <div class="card-body">
            <h4>Авторизиации пользователя</h4>

            Телефон: {{$user->phone??"Не указан"}}
            <div class="overflow-y-scroll border p-2" style="height: 200px;">
                @foreach(\SlavaWins\AuthSms\Models\PhoneVertify::where("user_id", $user->id)->get() as $V)

                    Поытка входа: {{$V->created_at}}
                    <BR> Попыток: {{$V->try_count}}
                    <BR> ip: {{$V->ip}}
                    <BR>       <BR>
                @endforeach
            </div>
        </div>
    </div>

</div>
