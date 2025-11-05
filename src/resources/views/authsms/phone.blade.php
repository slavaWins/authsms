@extends('authsms::authsms.layout')

@php
    $issetErrors = $errors->all();
@endphp

@section('scripts')

    <script>
        isSended = true;


        $(document).ready(function () {

            var inputLogin = $('.inp_login>input');

            $('.isLoading').hide();
            $('.inp_phone_auth').show();

            $('.inp_login>input').mask('(000) 000-00-00');

            var isSended = false;
            var isAutoSendeingEnabled = true;

            AuthSms.CallByInputLen($('.inp_login>input'), 15, function () {
                if (!isAutoSendeingEnabled) return;
                if (isSended) return;

                $('.isLoading').show();
                $('.inp_phone_auth').hide();


                $('#formMain').submit();

                isSended = true;
            });


            function moveCaretToEnd(input) {
                const el = input[0]; // Получаем нативный DOM-элемент из jQuery объекта
                el.focus();
                el.setSelectionRange(el.value.length, el.value.length);
            }

            if(inputLogin.val().length> 0){
                console.log("not empty");
                moveCaretToEnd(inputLogin);
                isAutoSendeingEnabled=false;
                $('.btnSubmitLogin').show();
            }



            $('.inp_login>input').on('paste', function (e) {
                var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                var pastedText = clipboardData.getData('text');
                var cleanedText = pastedText.replace(/^\+7/, '');
                var cleanedText = pastedText.replace(/^\8/, '');

                if (pastedText.length < 10) return;

                e.preventDefault();
                console.log(cleanedText);
                $('.inp_login>input').val(cleanedText);
                $('.inp_login>input').trigger("input");

                return false;
            });

        });

    </script>
@endsection

@section('content_auth')

    <form method="POST" action="{{ route('auth.phone.send') }}" id="formMain" class="form_phone">
        @csrf


        <p class="_labelText">
            Введите номер телефона
        </p>

        @include('authsms::authsms.input-phone', ['ind'=>'login', 'prefix'=>"+7",'placeholder'=>'(999) 000-00-00', 'value'=>request("login")])


        @include('authsms::authsms.error-render')

        <button type="submit" class="btn btn-primary w-100 btnSubmitLogin" style="display:none;" >Отправить</button>


        @if(View::exists('services.socialite.auth'))
            @include('services.socialite.auth')
        @endif


        <p class=" isLoading " style=" display: none;">
            @include('authsms::authsms.spiner')
        </p>


        <p class="_labelPrivPol" >
            @include('authsms::authsms.policy-small-text')
        </p>

    </form>

@endsection
