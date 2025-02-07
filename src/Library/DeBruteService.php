<?php


namespace SlavaWins\AuthSms\Library;


use Illuminate\Support\Facades\RateLimiter;

class DeBruteService
{

    /**
     * Проверить на брут с этого ip
     * @param $ind
     * @param string $ip если не вводить, то будет REMOTE_ADDR юзаться
     * @return bool
     */
    public static function IsBrutoforce($ind, $ip = null)
    {


        if ($ip == null) $ip = request()->ip();

        if (config("authsms.AttemptsMaxByIp") > 0) {

            $limitKey = $ind . '__' . $ip;

            if (RateLimiter::tooManyAttempts($limitKey, config("authsms.AttemptsMaxByIp"))) {
                $seconds = RateLimiter::availableIn($limitKey);

                return $seconds;
            }

            RateLimiter::hit($limitKey, config("authsms.WaitInSecondsIsBrut", 6000));
        }


        return false;
    }


    /**
     *
     * @param $login
     * @return bool
     */
    public static function IsGlobalBrutoforce($login)
    {


        if (config("authsms.GlobalBrutoforcePerLogin") > 0) {

            $limitKey = "globalbrute__" . $login;

            if (RateLimiter::tooManyAttempts($limitKey, config("authsms.GlobalBrutoforcePerLogin"))) {
                $seconds = RateLimiter::availableIn($limitKey);

                return $seconds;
            }

            RateLimiter::hit($limitKey, 60 * 15);
        }


        return false;
    }

    /**
     *
     * @param $login
     * @return bool
     */
    public static function IsGlobalBrutoforceAll()
    {


        if (config("authsms.GlobalBrutoforceForAll") > 0) {

            $limitKey = "globalbrute___all";

            if (RateLimiter::tooManyAttempts($limitKey, config("authsms.GlobalBrutoforceForAll"))) {
                $seconds = RateLimiter::availableIn($limitKey);

                return $seconds;
            }

            RateLimiter::hit($limitKey, 60 * 15);
        }


        return false;
    }

}
