<?php

return [



    //Массовая атака на все номера и логины
    'AUTHSMS_USE_MAIL' => env("AUTHSMS_USE_MAIL"),


    'AUTHSMS_TEST_MODE' => env("AUTHSMS_TEST_MODE"),


    //Позволять регистрировать новые логины. Если у чела нет аккаунта он создастся после подтвреждения кода
    'AUTHSMS_AUTO_REGISTRATION' => env("AUTHSMS_AUTO_REGISTRATION"),


    //Массовая атака на все номера и логины, колв поыпток
    'GlobalBrutoforceForAll' => intval(  env("AUTHSMS_TEST_GlobalBrutoforceForAll") ?? env("AUTHSMS_LIMIT_GlobalBrutoforceForAll", 35)),

    //Массовая брут атака на конкретный логин
    'GlobalBrutoforcePerLogin' => intval(  env("AUTHSMS_TEST_GlobalBrutoforcePerLogin") ??  env("AUTHSMS_LIMIT_GlobalBrutoforcePerLogin", 5)),

    //Лимит попыток на один ип
    'AttemptsMaxByIp' => intval(  env("AUTHSMS_TEST_AttemptsMaxByIp") ?? env("AUTHSMS_LIMIT_AttemptsMaxByIp", 3)),

    //Сколько ждать если было много попыток с одного ип
    'WaitInSecondsIsBrut' => intval( env("AUTHSMS_TEST_WaitInSecondsIsBrut") ?? env("AUTHSMS_LIMIT_WaitInSecondsIsBrut", 60 * 16)),


    'IsCheckIpEqCodeLogin' => env("AUTHSMS_CHECK_EQ_IP", true),

    'PreappenPhoneCode' => env("AUTHSMS_TEST_PREAPPEND_PHONE_CODE", ""),
];
