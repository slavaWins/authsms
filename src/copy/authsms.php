<?php

/*
 * Конфиг пакета slavawins/authsms.
 * Пакет читает настройки ТОЛЬКО отсюда (через config()), поэтому всё работает и после `php artisan config:cache`.
 */
return [

    // Вход по email (код приходит письмом) вместо телефона
    'AUTHSMS_USE_MAIL' => (bool)env("AUTHSMS_USE_MAIL", false),

    // Код всегда 1111. На production игнорируется (см. AuthSmsConfig::IsTestMode)
    'AUTHSMS_TEST_MODE' => (bool)env("AUTHSMS_TEST_MODE", false),

    // Позволять регистрировать новые логины. Если у чела нет аккаунта он создастся после подтверждения кода
    'AUTHSMS_AUTO_REGISTRATION' => (bool)env("AUTHSMS_AUTO_REGISTRATION", false),

    // Вход по паролю. Код (SMS/почта) нужен только при регистрации или первой установке пароля
    'AUTHSMS_USE_ONLY_PASSWORD' => (bool)env("AUTHSMS_USE_ONLY_PASSWORD", false),

    // Режим разработки: войти можно только с этим телефоном/email. Пусто — без ограничения
    'AUTHSMS_USE_ONLY_PHONE' => env("AUTHSMS_USE_ONLY_PHONE", ""),

    // Выключить все лимиты (для QA). На production игнорируется
    'AUTHSMS_LIMIT_DISABLED_QA' => (bool)env("AUTHSMS_LIMIT_DISABLED_QA", false),

    // Брут-атака на конкретный логин со всех IP: попыток за 15 минут (0 — выкл)
    'GlobalBrutoforcePerLogin' => intval(env("AUTHSMS_GLOBAL_BRUT_PER_LOGIN") ?? env("AUTHSMS_TEST_GlobalBrutoforcePerLogin") ?? env("AUTHSMS_LIMIT_GlobalBrutoforcePerLogin", 5)),

    // Лимит попыток на один IP (0 — выкл)
    'AttemptsMaxByIp' => intval(env("AUTHSMS_ATTEMPTS_MAX_MY_IP") ?? env("AUTHSMS_TEST_AttemptsMaxByIp") ?? env("AUTHSMS_LIMIT_AttemptsMaxByIp", 3)),

    // Сколько ждать, если было много попыток с одного IP, сек
    'WaitInSecondsIsBrut' => intval(env("AUTHSMS_BRUT_COOLDOWN_SEC") ?? env("AUTHSMS_TEST_WaitInSecondsIsBrut") ?? env("AUTHSMS_LIMIT_WaitInSecondsIsBrut", 60 * 16)),

    // Код должен вводиться с того же IP, с которого запрошен
    'IsCheckIpEqCodeLogin' => (bool)env("AUTHSMS_CHECK_EQ_IP", true),

    'PreappenPhoneCode' => env("AUTHSMS_PREAPPEND_PHONE_CODE") ?? env("AUTHSMS_TEST_PREAPPEND_PHONE_CODE", ""),
];
