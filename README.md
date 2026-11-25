## AuthSms

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

#Можно использовать почту, и отправку на почту кода. Аналогично СМС
AUTHSMS_USE_MAIL=false

#Разрешать вход только с одного номера или оставить поле пустым
AUTHSMS_USE_ONLY_PHONE=9141111111

#Если Превышено число поыток то сколько ждать челу до след попытки
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



## Tests


       <testsuite name="AuthSms">
            <directory suffix="Test.php">./vendor/slavawins/authsms/src/IntegrationTests</directory>
        </testsuite>    

Что затестить отдельно сервис

    php artisan test --testsuite=AuthSms


## Документация

Вход и регистрация по одноразовому коду из SMS (или email) поверх стандартного `App\Models\User`.

### Как работает
1. `GET /auth` — форма ввода номера (или email).
2. `POST /auth` (`auth.phone.send`) — валидация номера (10 цифр, libphonenumber, регион RU), лимиты, создание `PhoneVertify` (код хранится хешем, к нему привязан IP) и отправка SMS через `App\Actions\AuthSms\SendSms`.
3. `POST /auth/code/{phonevertify}` (`auth.code.send`) — проверка кода: 3 минуты, 2 попытки, тот же IP. Если пользователь найден, выполняется вход. Если нет и включена авторегистрация, он создаётся через `App\Actions\AuthSms\CreateNewUser`. После этого `Auth::login` и редирект на `home`.
4. Email-режим: `POST /auth-email` (`auth.email.send`) делает то же самое, только код уходит письмом через `App\Mail\AnyEmailMailable`.
5. Режим пароля (`AUTHSMS_USE_ONLY_PASSWORD=true`): `POST /auth/password/{phonevertify}`.
   - пользователь есть и у него задан пароль → код не отправляется, поле `password`;
   - регистрация или пароля ещё нет → код уходит SMS/письмом, нужны `code` + новый `password` (от 8 символов).
   Лимиты те же, что для кода: по IP, по логину, 2 попытки на одну выданную попытку входа.

### Подключение
```php
use SlavaWins\AuthSms\Library\AuthSmsRoute;
AuthSmsRoute::routes();          // имя GET-роута по умолчанию "login"
AuthSmsRoute::routes('auth');    // как в t-engine (через AuthenticationSwitchService)
```
Публикация: `php artisan vendor:publish --provider="SlavaWins\AuthSms\Providers\AuthSmsServiceProvider"`. Публикуются вьюхи, js, миграции и `app/Actions/AuthSms/*`.
Конфиг `config/authsms.php` копируется вручную из `src/copy/authsms.php`.
Пакет читает **только** `config('authsms.*')` (класс `SlavaWins\AuthSms\Library\AuthSmsConfig`), поэтому работает с `config:cache`.

### Точки расширения (в проекте)
- `App\Actions\AuthSms\SendSms::send($phone, $message): bool` — отправка SMS (в t-engine через `SmsFacade`).
- `App\Actions\AuthSms\CreateNewUser::create(): User` — создание нового пользователя.
- `resources/views/authsms/*` — внешний вид, `layout.blade.php` — родительский layout.

### Настройки (`config/authsms.php` / `.env`)
| Ключ конфига | env | По умолчанию | Смысл |
|---|---|---|---|
| `AUTHSMS_USE_MAIL` | `AUTHSMS_USE_MAIL` | — | код на email вместо SMS |
| `AUTHSMS_TEST_MODE` | `AUTHSMS_TEST_MODE` | false | код всегда `1111` (**на production игнорируется**) |
| `AUTHSMS_AUTO_REGISTRATION` | `AUTHSMS_AUTO_REGISTRATION` | — | создавать пользователя, если его нет |
| `AUTHSMS_LIMIT_DISABLED_QA` | `AUTHSMS_LIMIT_DISABLED_QA` | false | выключить все лимиты (**на production игнорируется**) |
| `AUTHSMS_USE_ONLY_PASSWORD` | `AUTHSMS_USE_ONLY_PASSWORD` | false | вход по паролю (код только при регистрации) |
| `AUTHSMS_USE_ONLY_PHONE` | `AUTHSMS_USE_ONLY_PHONE` | "" | пускать только этот телефон/email (разработка) |
| `GlobalBrutoforcePerLogin` | `AUTHSMS_GLOBAL_BRUT_PER_LOGIN` | 5 / 15 мин | лимит на один номер/email |
| `AttemptsMaxByIp` | `AUTHSMS_ATTEMPTS_MAX_MY_IP` | 3 | лимит на IP (0 — выкл) |
| `WaitInSecondsIsBrut` | `AUTHSMS_BRUT_COOLDOWN_SEC` | 960 | бан по IP, сек |
| `IsCheckIpEqCodeLogin` | `AUTHSMS_CHECK_EQ_IP` | true | код вводится с того же IP |
| `PreappenPhoneCode` | `AUTHSMS_PREAPPEND_PHONE_CODE` | "" | префикс к номеру |

Общего лимита на весь сайт больше нет (`GlobalBrutoforceForAll` удалён) — это был DoS-вектор.

### Тесты
Добавьте в `phpunit.xml` проекта (сейчас там их нет):
```xml
<testsuite name="AuthSms">
    <directory suffix="Test.php">./vendor/slavawins/authsms/src/IntegrationTests</directory>
</testsuite>
```
`php artisan test --testsuite=AuthSms`. Тесты покрывают брутфорс, лимит на логин, режим пароля (`PasswordModeAuthTest`), протухание и повторное использование кода, подмену IP, SQLi/timing/race-сценарии.
