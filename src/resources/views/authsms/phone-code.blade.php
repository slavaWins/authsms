@extends('authsms.layout')


@section('scripts')
    <script>
        $('.inp_code>input').mask('0000');

        var isSended = false;
        $('.inp_code>input').on("keyup", function () {
            if (isSended) return;
            if ($(this).val().length == 4) {
                isSended = true;
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

    <form method="POST" action="{{ route('auth.code.send', $phonevertify) }}" id="formMain">
        @csrf


        <p class="text-center mb-0" style=" font-size: 18px; color:#000;">
            Введите код
        </p>
        <small class="mb-1">
            Отправленный на номер {{$phone_draw}}
        </small>

        @include('authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXX'])

        <button type="submit" class="mt-4 btn btn-primary col-12 p-3 shadow-0">
            Отправить
        </button>

        <p class="mt-2" style="font-size: 11px; line-height: 1em;">
            Нажимая «Далее», вы принимаете пользовательское соглашение и соглашаетесь на обработку вашей персональной
            информации на условиях политики конфиденциальности
        </p>

    </form>

@endsection
