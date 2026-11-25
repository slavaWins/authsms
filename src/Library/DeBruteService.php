<?php


namespace SlavaWins\AuthSms\Library;


use Illuminate\Support\Facades\RateLimiter;

class DeBruteService
{

    /**
     * Лимит попыток с одного IP.
     * @param string $ind    ключ действия ("sms", "checkLogins", ...)
     * @param string|null $ip если не указан — request()->ip()
     * @return int|false     сколько секунд ждать, или false если лимит не превышен
     */
    public static function IsBrutoforce($ind, $ip = null)
    {
        if ($ip == null) $ip = request()->ip();

        $max = (int)config("authsms.AttemptsMaxByIp");
        if ($max <= 0) return false;

        return self::Hit($ind . '__' . $ip, $max, (int)config("authsms.WaitInSecondsIsBrut", 60 * 16));
    }

    /**
     * Произвольный лимит по IP
     * @return int|false
     */
    public static function IsBrutoforceCustom($ind, $tryCount = 5, $waitTime = 6000)
    {
        return self::Hit($ind . '__' . request()->ip(), (int)$tryCount, (int)$waitTime);
    }


    /**
     * Лимит на конкретный логин (телефон/email) со всех IP.
     * Защищает от перебора через пул прокси; побочный эффект — аккаунт можно временно «заморозить» (осознанный выбор).
     * @return int|false
     */
    public static function IsGlobalBrutoforce($login)
    {
        $max = (int)config("authsms.GlobalBrutoforcePerLogin");
        if ($max <= 0) return false;

        return self::Hit("globalbrute__" . mb_strtolower((string)$login), $max, 60 * 15);
    }

    /**
     * @deprecated Общий лимит на весь сайт убран: любой мог одним скриптом закрыть вход всем. Всегда false.
     */
    public static function IsGlobalBrutoforceAll()
    {
        return false;
    }


    /**
     * @return int|false
     */
    private static function Hit(string $limitKey, int $max, int $decaySeconds)
    {
        if (RateLimiter::tooManyAttempts($limitKey, $max)) {
            return max(1, RateLimiter::availableIn($limitKey));
        }

        RateLimiter::hit($limitKey, $decaySeconds);
        return false;
    }

}
