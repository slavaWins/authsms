<?php


namespace SlavaWins\AuthSms\IntegrationTests;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use SlavaWins\AuthSms\Library\AuthSmsConfig;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Tests\TestCase;

/**
 * Режим AUTHSMS_USE_ONLY_PASSWORD: регистрация только с подтверждением кода, вход по паролю.
 */
class PasswordModeAuthTest extends TestCase
{
    private string $phone = "9999999877";

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        PhoneVertify::where("id", ">", 0)->delete();
        User::where("phone", $this->phone)->delete();

        config([
            "authsms.AUTHSMS_USE_ONLY_PASSWORD" => true,
            "authsms.AUTHSMS_AUTO_REGISTRATION" => true,
            "authsms.AUTHSMS_USE_MAIL" => false,
            "authsms.AUTHSMS_TEST_MODE" => true,
            "authsms.AttemptsMaxByIp" => 20,
        ]);
    }

    public function IsErrorResponse()
    {
        if (!session()->has('errors')) return null;
        return session()->get('errors')->first();
    }

    private function lastTry(): PhoneVertify
    {
        return PhoneVertify::orderByDesc("id")->first();
    }

    public function test_RegisterRequiresCode()
    {
        $result = $this->post("/auth", ['login' => $this->phone]);
        $this->assertEquals(200, $result->status());
        $this->assertStringContainsString("Регистрация", $result->getContent());

        $try = $this->lastTry();

        // Без кода — нельзя (раньше можно было занять чужой номер одним паролем)
        $this->post("/auth/password/" . $try->id, ['password' => 'secret123']);
        $this->assertNotNull($this->IsErrorResponse());
        $this->assertNull(\Auth::user());
        $this->assertFalse(User::where("phone", $this->phone)->exists());

        // Неверный код
        $this->post("/auth/password/" . $try->id, ['code' => '1112', 'password' => 'secret123']);
        $this->assertEquals("Не верный код попробуйте ещё раз", $this->IsErrorResponse());
        $this->assertNull(\Auth::user());

        // Короткий пароль
        $this->post("/auth/password/" . $try->id, ['code' => '1111', 'password' => '123']);
        $this->assertNotNull($this->IsErrorResponse());
        $this->assertNull(\Auth::user());

        // Верный код + нормальный пароль
        $this->post("/auth/password/" . $try->id, ['code' => '1111', 'password' => 'secret123']);
        $this->assertNotNull(\Auth::user());

        $user = User::where("phone", $this->phone)->first();
        $this->assertTrue(Hash::check('secret123', $user->password));

        // Попытка одноразовая
        \Auth::logout();
        $this->post("/auth/password/" . $try->id, ['code' => '1111', 'password' => 'secret123']);
        $this->assertEquals("Код устарел, повторите авторизацию", $this->IsErrorResponse());
        $this->assertNull(\Auth::user());
    }

    public function test_LoginByPassword()
    {
        $this->test_RegisterRequiresCode();
        \Auth::logout();
        Cache::flush();

        $result = $this->post("/auth", ['login' => $this->phone]);
        $this->assertEquals(200, $result->status());
        $this->assertStringContainsString("Введите пароль", $result->getContent());

        $try = $this->lastTry();

        $this->post("/auth/password/" . $try->id, ['password' => 'wrong-pass']);
        $this->assertEquals("Не правильный пароль", $this->IsErrorResponse());
        $this->assertNull(\Auth::user());

        $this->post("/auth/password/" . $try->id, ['password' => 'secret123']);
        $this->assertNotNull(\Auth::user());
    }

    public function test_PasswordBruteLimitedPerTry()
    {
        $this->test_RegisterRequiresCode();
        \Auth::logout();
        Cache::flush();

        $this->post("/auth", ['login' => $this->phone]);
        $try = $this->lastTry();

        for ($i = 0; $i < 2; $i++) {
            $this->post("/auth/password/" . $try->id, ['password' => 'wrong' . $i]);
        }

        // Третья попытка — уже даже верный пароль не пройдёт
        $this->post("/auth/password/" . $try->id, ['password' => 'secret123']);
        $this->assertEquals("Не осталось попыток.", $this->IsErrorResponse());
        $this->assertNull(\Auth::user());
    }

    public function test_TestModeIgnoredOnProduction()
    {
        config(["authsms.AUTHSMS_TEST_MODE" => true, "authsms.AUTHSMS_LIMIT_DISABLED_QA" => true]);

        $env = app()['env'];
        app()['env'] = 'production';
        try {
            $this->assertFalse(AuthSmsConfig::IsTestMode());
            $this->assertFalse(AuthSmsConfig::IsLimitsDisabled());
        } finally {
            app()['env'] = $env;
        }

        $this->assertTrue(AuthSmsConfig::IsTestMode());
    }
}
