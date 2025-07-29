@extends('authsms::authsms.layout')


@section('scripts')
    <script>

        var inpCode = $('.inp_code>input');
        var urlCodeSend = "{{ route('auth.code.send', $phonevertify) }}";
        var isSending = false;

        function HandleLenght() {
            AuthSms.CallByInputLen(inpCode, 4, function () {
                console.log("n");
                SubmitCode();
            });
            inpCode.focus();
        }
        function SubmitCode() {
            if (isSending) return;
            $('.isLoading').show();
            $('.inp_code').hide();
            isSending = true;
            $('.error-auth-render').hide();
            EasyApi.Post(urlCodeSend, {
                code: inpCode.val()
            }, function (r, e) {

                isSending = false;
                inpCode.val("");
                $('.isLoading').hide();

                console.log(r, e);

                if (e) {
                    $('.error-auth-render').show();
                    $('.error-auth-render').text(e);
                    $('.inp_code').show();
                    HandleLenght();

                    if (r['code'] == "resend") {
                        setTimeout(()=>location.href= "", 800);
                    }

                    return;
                }

                location.href=  r;

            });
        }


        $(document).ready(function () {
            inpCode.mask('0000');
            HandleLenght();
        });
    </script>
@endsection

@section('content_auth')

    <p class="text-center mb-0" style=" font-size: 18px; ">
        Введите код
    </p>
    <div class="mb-2 text-center small ">
        Отправленный на {{$phone_draw}}
    </div>

    @include('authsms::authsms.input-phone', ['ind'=>'code','placeholder'=>'XXXX', 'isDisabledAutoComplite'=>true])

    <div class="error-auth-render   " style="color:#ff2f00; font-size: 0.8em; display:none;">
        Ett
    </div>
    @include('authsms::authsms.error-render')


    <p class="text-center isLoading mb-4" style=" font-size: 32px;  display: none;">
         <span class="spinner _contentAttachSpiner spinner-border" style="width: 1.4rem; height: 1.4rem; "
               role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>

    </p>

@endsection
