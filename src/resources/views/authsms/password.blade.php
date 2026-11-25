@extends('authsms::authsms.layout')


@section('scripts')
    <script>
        $(document).ready(function () {
            @if($needCode ?? $isRegister)
            $('.inp_code>input').mask('0000');
            @endif
        });
    </script>
@endsection

@section('content_auth')

    <form method="POST" action="{{ route('auth.password.send', $phonevertify) }}" id="formMain">
        @csrf


        <p class="text-center mb-0" style=" font-size: 18px; color:#000;">
            @if($isRegister)
                Регистрация
            @else
                Введите пароль
            @endif
        </p>

        @if($isRegister)
            <p class="mb-1 small text-center">
                Мы отправили код на <span>{{$phone_draw}}</span>.<BR>
                Введите его и придумайте пароль (от 8 символов).
            </p>

            @include('authsms::authsms.input-phone', ['ind'=>'code','placeholder'=>'Код XXXX', 'type'=>'text'])
            <div class="mt-2"></div>
        @endif

        @include('authsms::authsms.input-phone', ['ind'=>'password','placeholder'=>'Пароль', 'type'=>'password', 'value'=>''])

        @include('authsms::authsms.error-render')

        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0 btn-submit-auth">
            Отправить
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            @include('authsms::authsms.policy-small-text')
        </p>

    </form>

@endsection
