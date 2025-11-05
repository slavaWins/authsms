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


    <form method="POST" action="{{ route('auth.code.send', $phonevertify) }}" id="formMain" class="form_code">
        @csrf


        <p class="_labelText">
            Введите код
        </p>
        <p class="_labelSendTo">
            Отправленный на: <BR> <span>{{$phone_draw}}</span>
        </p>

        @include('authsms::authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXX'])

        @include('authsms::authsms.error-render')




        <p class=" isLoading " style=" display: none;">
            @include('authsms::authsms.spiner')
        </p>


        <p class="_labelPrivPol" >
            @include('authsms::authsms.policy-small-text')
        </p>

    </form>

@endsection
