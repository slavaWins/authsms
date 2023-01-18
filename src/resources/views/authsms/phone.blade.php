@extends('authsms.layout')

@section('scripts')
    <script>
        $('.inp_login>input').mask('(000) 000-00-00');

        AuthSms.CallByInputLen( $('.inp_login>input'), 15, function (){
            $('#formMain').submit();
        });
    </script>
@endsection

@section('content_auth')






    <form method="POST" action="{{ route('auth.phone.send') }}" id="formMain">
        @csrf


        <p class="text-center mb-4" style=" font-size: 18px; color:#000;">
            Введите номер телефона
        </p>

        @include('authsms.input-phone', ['ind'=>'login', 'prefix'=>"+7",'placeholder'=>'(999) 000-00-00'])


        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0 btn-submit-auth">
            Вход
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях политики конфиденциальности
        </p>

    </form>

@endsection
