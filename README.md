<p align="center">
<img src="info/logo.png">
</p>
 
## Auth Sms
Кароч изи пакет авторизации по смс. Ставится поверх системы пользователей для ларавела.
Сразу есть вью, красивый инпут, даже фон. Как на картинке - из коробки.
Там боотсрап 5й.
   

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

#Колв попыток лимитированое одним ip. Поставить 0, что бы отключить антибрут по ip
AUTHSMS_TEST_AttemptsMaxByIp = 4

#Ключ от апи sms.ru
AUTHSMS_SMSRU_API_KEY=xxx

#Вместо смс, использовать просто пароль?
AUTHSMS_USE_ONLY_PASSWORD=false

#Разрешать вход только с одного номера или оставить поле пустым
AUTHSMS_USE_ONLY_PHONE=9141111111

#Если привышено число поыток то сколько ждать челу до след попытки
AUTHSMS_TEST_WaitInSecondsIsBrut=120
 ``` 
Для подключение апи отправки СМС, перейдите на:	http://zxc76.sms.ru/



4) В роутере routes/web.php удалить:
 ```
    Auth::routes();
 ``` 
И добавить
 ```
    AuthSmsRoute::routes();
 ``` 



5) Выполнить миграцию
 ```
    php artisan migrate 
 ``` 


6) В папке resources\views\authsms\layout.blade.php  нужно указать ваш layout который вы используете.
И заменить app-col на "content". Вообщем нужно сделать так как оно должно работать у вас.
 ```
  @extends('layouts.app')
  
  @section('app-col')
 ``` 

 
7) Пользователь создается в экшен классе app\Actions\AuthSms\CreateNewUser.php
Там вы можете указать кастомные поля, и напримере если у вас что-то не может быть nullable и не имеет defualt value. 
