@extends('authsms::authsms.layout')


@section('scripts')
    <script>
        $(document).ready(function () {
        $('.inp_code>input').mask('0000');

        var isSended = false;
        AuthSms.CallByInputLen( $('.inp_code>input'), 4, function (){
            if (isSended) return;

            $('.isLoading').show();
            $('.inp_code').hide();
            $('#formMain').submit();
            isSended = true;
        });

        });
    </script>
@endsection

@section('content_auth')


    <form method="POST" action="{{ route('auth.code.send', $phonevertify) }}" id="formMain">
        @csrf


        <p class="text-center mb-0" style=" font-size: 18px; ">
            Введите код
        </p>
        <div class="mb-2 text-center small ">
            Отправленный на: <BR> {{$phone_draw}}
        </div>

        @include('authsms::authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXX'])

        @include('authsms::authsms.error-render')


        <p class="text-center isLoading mb-4" style=" font-size: 18px; color:#000; display: none;">
         <span class="spinner _contentAttachSpiner spinner-border" style="width: 1.4rem; height: 1.4rem; "
               role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>

        </p>


        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях  <a target='_blank' href="{{route("privacy")}}">политики конфиденциальности</a>
        </p>

    </form>

@endsection
