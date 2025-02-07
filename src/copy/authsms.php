<?php

return [



    //Массовая атака на все номера и логины
    'AUTHSMS_USE_MAIL' => env("AUTHSMS_USE_MAIL"),


    'AUTHSMS_TEST_MODE' => env("AUTHSMS_TEST_MODE"),


    //Позволять регистрировать новые логины. Если у чела нет аккаунта он создастся после подтвреждения кода
    'AUTHSMS_AUTO_REGISTRATION' => env("AUTHSMS_AUTO_REGISTRATION"),


    //Массовая атака на все номера и логины, колв поыпток
    'GlobalBrutoforceForAll' => env("AUTHSMS_TEST_GlobalBrutoforceForAll", 35),

    //Массовая брут атака на конкретный логин
    'GlobalBrutoforcePerLogin' => env("AUTHSMS_TEST_GlobalBrutoforcePerLogin", 5),

    //Лимит попыток на один ип
    'AttemptsMaxByIp' => env("AUTHSMS_TEST_AttemptsMaxByIp", 3),

    //Сколько ждать если было много попыток с одного ип
    'WaitInSecondsIsBrut' => env("AUTHSMS_TEST_WaitInSecondsIsBrut", 60 * 15),
];
