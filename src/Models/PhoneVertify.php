<?php

    namespace SlavaWins\AuthSms\Models;

    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    /**
     * @property mixed $phone
     * @property int $try_count
     * @property int $id
     * @property string $code
     * @property string $ip
     * @property Carbon $last_try
     * @property Carbon $created_at
     * @property Carbon $updated_at
     * @property int $user_id
     * @property bool $is_sended_on_phone
     * @property User $user
     */
    class PhoneVertify extends Model
    {
        use HasFactory;


        protected $fillable = [
            'is_sended_on_phone',
            'phone',
            'try_count',
            'code',
            'user_id',
            'ip',
        ];

        protected $dates = [
            'created_at',
            'updated_at',
            'last_try',
        ];

        public function user() {
            return $this->belongsToMany(User::class);
        }
    }
