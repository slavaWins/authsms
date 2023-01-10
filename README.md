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

2) Опубликовать js файлы, вью и миграции необходимые для работы пакета.
Вызывать команду:
   ```
   php artisan vendor:publish --provider="SlavaWins\AuthSms\Providers\AuthSmsServiceProvider"
   ``` 



3) В env нужно указать настройки для плагина
 ```
#---- AuthSms Settings
#Если true то код смс всегда будет 1111
AUTHSMS_TEST_MODE=true

#Ключ от апи sms.ru
AUTHSMS_SMSRU_API_KEY=xxx
 ``` 



3) В роутере routes/web.php удалить:
 ```
    Auth::routes();
 ``` 
И добавить
 ```
    AuthSmsRoute::routes();
 ``` 



3) Подключить js файлы в любом удобном месте. Можно просто в app.blade.php
 ```
    <script src="{{ asset('js/authsms/XXX.js') }}"></script> 
 ``` 


 
