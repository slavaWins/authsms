<p align="center">
<img src="info/logo.png">
</p>
 
## Auth Sms
Кароч изи пакет авторизации по смс
   

## Установка
1) Установить из композера 
```  
composer require slavawins/authsms
```
composer dump-autoload
2) Опубликовать js файлы необходимые для работы пакета.
Вызывать команду:
   ```
   php artisan vendor:publish --provider="SlavaWins\AuthSms\Providers\AuthSmsServiceProvider"
   ``` 
После этого в папке public_html/js/ будут созданы джс файла нужные для использования пакета

3) Подключить js файлы в любом удобном месте. Можно просто в app.blade.php
 ```
    <script src="{{ asset('js/authsms/XXX.js') }}"></script> 
 ``` 


## Использваоние

В env нужно указать настройки для плагина
 ```
@php
    authsms.smsruapikey = XXX
    authsms.devemode = true #Значит то код смс всегда будет 1111
@endphp
 ```
 
