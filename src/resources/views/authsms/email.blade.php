@extends('authsms::authsms.layout')

@section('scripts')
    <script>
        $(document).ready(function () {

        var isSended = false;
        $('#formMain').on("submit", function () {
            if (isSended) return;

            $('.isLoading').show();
            $('.info_header').hide();
            $('.inp_phone_auth').hide();
            $('.btn-submit-auth').hide();
            isSended = true;
        });

        @if(!$errors->any())
            var endings = ["@mail.ru", "@gmail.com", "@bk.ru", "@list.ru", "@inbox.ru", "@yandex.ru"];
            $('.inp_login>input').on("keyup", function () {
                var mail = $('.inp_login>input').val();
                for (var i = 0; i < endings.length; i++) {
                    if (mail.endsWith(endings[i])) {
                        $('#formMain').submit();
                        return;
                    }
                }
            });
            $('.inp_login>input').trigger("keyup");

        });
        @endif
    </script>
@endsection

@section('content_auth')

    <form method="POST" action="{{ route('auth.email.send') }}" id="formMain" class="form_email">
        @csrf


        <p class="_labelText">
            Введите почту
        </p>

        @include('authsms::authsms.input-phone', ['ind'=>'login', 'placeholder'=>'xxxx@mail.ru', "type"=>"email", 'value'=>request("login")])

        @include('authsms::authsms.error-render')


        <p class=" isLoading " style=" display: none;">
            @include('authsms::authsms.spiner')
        </p>


        <button type="submit" class="btn btn-primary btnSubmitLogin btn-submit-auth">
            Отправить
        </button>

        @if(View::exists('services.socialite.auth'))
            @include('services.socialite.auth')
        @endif





        <p class="_labelPrivPol" >
            @include('authsms::authsms.policy-small-text')
        </p>


    </form>

@endsection
