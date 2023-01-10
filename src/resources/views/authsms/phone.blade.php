@extends('authsms.layout')

@section('scripts')
    <script>
        $('.inp_phone>input').mask('(000) 000-00-00');

        var isSended = false;
        $('.inp_phone>input').on("keyup", function () {
            if(isSended)return;
            if ($(this).val().length == 15) {
                isSended=true;
                $('#formMain').submit();
            }
        });
    </script>
@endsection

@section('content')

    <style>
        .inp_code input {
            text-align: center;
        }
    </style>




    <form method="POST" action="{{ route('auth.phone.send') }}" id="formMain">
        @csrf


        <p class="text-center mb-4" style=" font-size: 18px; color:#000;">
            Введите номер телефона
        </p>

        @include('components.input-phone', ['ind'=>'phone', 'prefix'=>"+7",'placeholder'=>'(999) 000-00-00'])


        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0">
            Вход
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях политики конфиденциальности
        </p>

    </form>

@endsection
