<?php

    namespace App\Actions\AuthSms;

    use Illuminate\Support\Facades\Log;

    class SendSms
    {

        public static function messageAuth($code) {
            return "Ваш код подтверждения ".$code.". Наберите его в поле ввода.";
        }

        public static function send($phone, $message) {

            $data = [
                'api_id'     => env("AUTHSMS_SMSRU_API_KEY"),
                'to'         => $phone,
                'json'       => 1,
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
                'translit'   => 1, //Транслит для эконмии бабла
                'partner_id' => 78435,
                'msg' => $message,
            ];

            $url = "https://sms.ru/sms/send?".http_build_query($data);

            // Таймаут, чтобы зависший sms.ru не держал воркер PHP. Ключ в URL — так требует API sms.ru, не логируем URL.
            $ctx = stream_context_create(['http' => ['timeout' => 10], 'https' => ['timeout' => 10]]);
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false) $body = "error";
            Log::info($body);

            if(substr_count($body, 'status": "OK')) return true;

            return false;
        }
    }
