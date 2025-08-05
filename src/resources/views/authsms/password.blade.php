@extends('authsms::authsms.layout')


@section('scripts')
    <script>
        $(document).ready(function () {
        $('.inp_code>input').mask('AAAAAA');

        AuthSms.CallByInputLen($('.inp_code>input'), 6, function () {
           $('#formMain').submit();
        });

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
            <p  class="mb-1 small text-center">Придумайте 6 значный пароль, для регистрации в сервисе.</p>
        @endif

        @include('authsms::authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXXХХ', 'type'=>'text'])

        @include('authsms::authsms.error-render')

        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0 btn-submit-auth">
            Отправить
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            @include('authsms::authsms.policy-small-text')
        </p>

    </form>

@endsection
