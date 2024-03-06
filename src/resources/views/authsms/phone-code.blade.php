@extends('authsms.layout')


@section('scripts')
    <script>
        $('.inp_code>input').mask('0000');

        AuthSms.CallByInputLen( $('.inp_code>input'), 4, function (){
            $('#formMain').submit();
        });
    </script>
@endsection

@section('content_auth')


    <form method="POST" action="{{ route('auth.code.send', $phonevertify) }}" id="formMain">
        @csrf


        <p class="text-center mb-0" style=" font-size: 18px; color:#000;">
            Введите код
        </p>
        <small class="mb-1">
            Отправленный на {{$phone_draw}}
        </small>

        @include('authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXX'])

        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0 btn-submit-auth">
            Отправить
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях политики конфиденциальности
        </p>

    </form>

@endsection
