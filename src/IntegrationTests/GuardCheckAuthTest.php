<?php


namespace SlavaWins\AuthSms\IntegrationTests;

use Illuminate\Support\Facades\Cache;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Tests\TestCase;

class GuardCheckAuthTest extends TestCase
{
    public function SetMyIpToRandomVpn()
    {
        $ipAddress = rand(101, 199) . '.' . rand(111, 999) . '.' . rand(11, 99) . '.' . rand(11, 99);
        $this->serverVariables = ['REMOTE_ADDR' => $ipAddress];
        $_SERVER['REMOTE_ADDR'] = $ipAddress;
    }


    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        PhoneVertify::where("id", ">", 0)->delete();

        config(["authsms.AUTHSMS_AUTO_REGISTRATION" => true]);
    }

    public function IsErrorResponse()
    {
        if (!session()->has('errors')) return null;
        return session()->get('errors')->first();
    }

    public function test_BruteForceAttack()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);
        config(["authsms.GlobalBrutoforcePerLogin" => 9]);

        // Запрашиваем код для телефона
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Пытаемся подобрать код методом перебора
        for ($i = 0; $i < 8; $i++) {
            $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => '' . rand(1000, 9999)]);
            if ($result->status() == 302 && $this->IsErrorResponse() === null) {
                $this->fail("Уязвимость: код был подобран методом перебора.");
            }
        }

        // Проверяем, что после 3 попыток код блокируется
        $this->assertEquals("Не осталось попыток.", $this->IsErrorResponse());


        //Уже правильный код не пройдет, потому что ак заблочен временно
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => '1111']);
        $this->assertEquals("Этот аккаунт временно не доступен. Повторить вход через 900 сек.", $this->IsErrorResponse());
    }

    public function test_ExpiredCodeAttack()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);

        // Запрашиваем код для телефона
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Имитируем устаревший код
        $getLastCode->created_at = $getLastCode->created_at->subMinutes(10);
        $getLastCode->save();

        // Пытаемся использовать устаревший код
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals("Код устарел, повторите авторизацию", $this->IsErrorResponse());
    }

    public function test_IPAddressSpoofing()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);

        // Запрашиваем код для телефона с одного IP
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Меняем IP-адрес
        $this->serverVariables = ['REMOTE_ADDR' => '192.168.1.2'];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';

        // Пытаемся использовать код с другого IP
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals("IP адреса с которых вы запросили код и ввели не совпадают", $this->IsErrorResponse());
    }

    public function test_InvalidCodeFormat()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);

        // Запрашиваем код для телефона
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Пытаемся ввести код в неверном формате
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "ABCD"]);
        $this->assertEquals("Поле код имеет ошибочный формат.", $this->IsErrorResponse());
    }
}
