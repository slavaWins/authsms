<?php

namespace SlavaWins\AuthSms\Library;

use App\Mail\AnyEmailMailable;
use Illuminate\Support\Facades\Mail;

class SendEmail
{


    public static function send($email, $code)
    {


        $text = "На этот адрес электронной почты был отправлен четырехзначный код безопасности. Пожалуйста, введите этот код на странице входа, чтобы завершить процесс аутентификации.";

        $text .= "\n\n # " . $code;


        $text .= "\n\n Если вы не запрашивали код, пожалуйста, проигнорируйте это сообщение.";


        $res = Mail::to($email)->send(new AnyEmailMailable("🔑 Ваш код подтверждения для входа в учетную запись ", $text));


        return true;
    }
}
