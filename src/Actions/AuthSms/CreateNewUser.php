<?php

namespace App\Actions\AuthSms;

use App\Models\User;

class CreateNewUser
{
    /**
     * @return User
     */
    public static function create()
    {
        $user = new User();
        $user->name = "Новый пользователь";
        $user->email = time(); //if use unique
        $user->password = "";
        $user->save();

        //NotifyBallController::SendToUid($user->id, "Спасибо за регистрацию! Обязательно заполните свой профиль!", route('profile'));
        return $user;
    }
}
