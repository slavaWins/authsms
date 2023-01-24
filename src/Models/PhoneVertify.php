<?php

namespace SlavaWins\AuthSms\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $phone
 * @property int $try_count
 * @property int $is_closed
 * @property string $custom_data
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


    protected $casts=[
        'custom_data'=>'array',
    ];
    
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


    public static function MakeTryByPhone($phone, $ip = null)
    {
        $phonevertify = PhoneVertify::where("phone", $phone)->where("is_closed", false)->first();

        if ($phonevertify) {

            $phonevertify->last_try = Carbon::now();
        } else {
            $phonevertify = new PhoneVertify();
            $phonevertify->try_count = 0;
            $phonevertify->phone = $phone;
            if ($ip) {
                $phonevertify->ip = $ip;
            } 
            $phonevertify->code = rand(1000, 9999); 
        }

        $phonevertify->save();
        
        return $phonevertify;
    }


    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
