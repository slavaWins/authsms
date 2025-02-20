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

    <form method="POST" action="{{ route('auth.email.send') }}" id="formMain">
        @csrf


        <p class="text-center mb-4 info_header" style=" font-size: 18px; ">
            Введите почту
        </p>

        @include('authsms::authsms.input-phone', ['ind'=>'login', 'placeholder'=>'xxxx@mail.ru', "type"=>"email", 'value'=>request("login")])

        <p class="text-center isLoading mb-4" style=" font-size: 18px;  display: none;">
         <span class="spinner _contentAttachSpiner spinner-border" style="width: 1.4rem; height: 1.4rem; "
               role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>

        </p>

        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0 btn-submit-auth">
            Отправить
        </button>

        @if(View::exists('services.socialite.auth'))
            @include('services.socialite.auth')
        @endif

        <p class="mt-3" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях <a target='_blank' href="{{route("privacy")}}">политики конфиденциальности</a>
        </p>

    </form>

@endsection
