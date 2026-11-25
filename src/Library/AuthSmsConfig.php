<?php

namespace SlavaWins\AuthSms\Library;

use Illuminate\Support\Facades\Log;

/**
 * Все настройки пакета читаются только через config('authsms.*'):
 * env() вне config/*.php после `php artisan config:cache` возвращает null.
 */
class AuthSmsConfig
{
    /** Время жизни кода / попытки входа, сек */
    public const CODE_LIFETIME_SEC = 180;

    /** Код всегда 1111. На production игнорируется. */
    public static function IsTestMode(): bool
    {
        $on = (bool)config('authsms.AUTHSMS_TEST_MODE', false);
        if ($on && app()->isProduction()) {
            Log::warning('authsms: AUTHSMS_TEST_MODE=true на production — игнорируется');
            return false;
        }
        return $on;
    }

    /** Все лимиты выключены (для QA). На production игнорируется. */
    public static function IsLimitsDisabled(): bool
    {
        $on = (bool)config('authsms.AUTHSMS_LIMIT_DISABLED_QA', false);
        if ($on && app()->isProduction()) {
            Log::warning('authsms: AUTHSMS_LIMIT_DISABLED_QA=true на production — игнорируется');
            return false;
        }
        return $on;
    }

    /** Вход по email вместо телефона */
    public static function UseMail(): bool
    {
        return (bool)config('authsms.AUTHSMS_USE_MAIL', false);
    }

    /** Режим «пароль»: код нужен только при регистрации / первой установке пароля */
    public static function UsePasswordOnly(): bool
    {
        return (bool)config('authsms.AUTHSMS_USE_ONLY_PASSWORD', false);
    }

    public static function AutoRegistration(): bool
    {
        return (bool)config('authsms.AUTHSMS_AUTO_REGISTRATION', false);
    }

    /** Если задан — войти можно только с этим логином (режим разработки) */
    public static function OnlyLogin(): ?string
    {
        $v = config('authsms.AUTHSMS_USE_ONLY_PHONE');
        if ($v === null || $v === false || $v === '') return null;
        return mb_strtolower(trim((string)$v));
    }

    /** Колонка users, по которой ищем пользователя */
    public static function LoginField(): string
    {
        return self::UseMail() ? 'email' : 'phone';
    }
}
