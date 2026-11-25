<?php

namespace SlavaWins\AuthSms\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use SlavaWins\AuthSms\Library\AuthSmsConfig;

/**
 * Попытка входа: на какой логин (телефон или email — колонка phone) и с какого IP запрошен код.
 *
 * @property mixed $phone
 * @property int $try_count
 * @property int $is_closed
 * @property string $custom_data
 * @property int $id
 * @property string $code     HMAC кода, не сам код
 * @property string $ip
 * @property Carbon $last_try
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property bool $is_sended_on_phone
 */
class PhoneVertify extends Model
{

    protected $casts = [
        'custom_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_try' => 'datetime',
    ];

    protected $fillable = [
        'is_sended_on_phone',
        'phone',
        'try_count',
        'code',
        'user_id',
        'ip',
    ];

    /** Код истёк или попытка уже использована */
    public function IsExpired(): bool
    {
        if ($this->is_closed) return true;
        if (!$this->created_at) return true;

        return Carbon::now()->greaterThan($this->created_at->copy()->addSeconds(AuthSmsConfig::CODE_LIFETIME_SEC));
    }

    public function IsCodeEqals($code1): bool
    {
        if (!is_string($this->code) || $this->code === '') return false;
        if (!is_scalar($code1)) return false;

        return hash_equals($this->code, $this->CodeHashCode((string)$code1));
    }

    /**
     * HMAC-SHA256 от кода, привязанный к IP и логину. Без APP_KEY по слитой БД код не восстановить.
     */
    public function CodeHashCode($code): string
    {
        $payload = ((string)$code) . '|' . $this->ip . '|' . mb_strtolower((string)$this->phone);
        return hash_hmac('sha256', $payload, (string)config('app.key'));
    }

    public function SetCode($code)
    {
        $this->code = $this->CodeHashCode($code);
    }

    /** 4 цифры, криптостойкий генератор, 0000–9999 */
    public static function GetRandomCode(): string
    {
        return str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function MakeTryByPhone($phone, $ip, $code)
    {
        $phonevertify = new PhoneVertify();
        $phonevertify->try_count = 0;
        $phonevertify->phone = $phone;
        $phonevertify->ip = $ip;
        $phonevertify->SetCode($code);
        $phonevertify->save();

        return $phonevertify;
    }


}
