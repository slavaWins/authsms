<?php


namespace SlavaWins\AuthSms\IntegrationTests;

use Illuminate\Support\Facades\Cache;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Tests\TestCase;

class SessionAttackAuthTest extends TestCase
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

    public function test_CodeReuseAttack()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);

        // Запрашиваем код для телефона
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Успешно авторизуемся
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals(302, $result->status());
        $this->assertNull($this->IsErrorResponse());
        $this->assertNotNull(\Auth::user());

        // Пытаемся использовать тот же код повторно
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals(302, $result->status());
        $this->assertEquals("Код устарел, повторите авторизацию", $this->IsErrorResponse());
    }

    public function test_SessionHijackingAttack()
    {
        config(["authsms.AUTHSMS_USE_MAIL" => false]);

        // Запрашиваем код для телефона
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Успешно авторизуемся
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals(302, $result->status());
        $this->assertNull($this->IsErrorResponse());
        $this->assertNotNull(\Auth::user());

        // Сохраняем идентификатор сессии
        $sessionId = session()->getId();

        $result = $this->get("/profile");
        $this->assertEquals(200, $result->status());

        // Выходим из системы
        \Auth::logout();
        $this->assertNull(\Auth::user());

        // Пытаемся использовать сохранённый идентификатор сессии для доступа к аккаунту
        $this->withSession(['_token' => $sessionId]);

        $result = $this->get("/profile");
        $this->assertEquals(302, $result->status()); // Должно быть перенаправление на страницу входа
        $this->assertNull(\Auth::user());
    }
}
