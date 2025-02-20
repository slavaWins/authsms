@extends('authsms::authsms.layout')

@php
    $issetErrors = $errors->all();
@endphp

@section('scripts')

    <script>
        isSended = true;


        console.log("St");
        $(document).ready(function () {

            $('.isLoading').hide();
            $('.inp_phone_auth').show();

            $('.inp_login>input').mask('(000) 000-00-00');

            var isSended = false;
            AuthSms.CallByInputLen($('.inp_login>input'), 15, function () {
                if (isSended) return;

                $('.isLoading').show();
                $('.inp_phone_auth').hide();


                $('#formMain').submit();

                isSended = true;
            });


        });

    </script>
@endsection

@section('content_auth')

    <form method="POST" action="{{ route('auth.phone.send') }}" id="formMain">
        @csrf


        <p class="text-center mb-4" style=" font-size: 18px; ">
            Введите номер телефона
        </p>

        @include('authsms::authsms.input-phone', ['ind'=>'login', 'prefix'=>"+7",'placeholder'=>'(999) 000-00-00', 'value'=>request("login")])

        @include('authsms::authsms.error-render')

        @if(View::exists('services.socialite.auth'))
            @include('services.socialite.auth')
        @endif


        <p class="text-center isLoading mb-4" style=" font-size: 18px;  display: none;">
         <span class="spinner _contentAttachSpiner spinner-border" style="width: 1.4rem; height: 1.4rem; "
               role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>

        </p>


        <p class="mt-3" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях <a target='_blank' href="{{route("privacy")}}">политики конфиденциальности</a>
        </p>

    </form>

@endsection
