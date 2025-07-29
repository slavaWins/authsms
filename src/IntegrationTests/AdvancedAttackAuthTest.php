<?php


namespace SlavaWins\AuthSms\IntegrationTests;

//use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Str;
use Tests\TestCase;

class AdvancedAttackAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        PhoneVertify::where("id", ">", 0)->delete();
        config(["authsms.AUTHSMS_AUTO_REGISTRATION" => true]);
    }

    /**
     * @param TestResponse $response
     * @return null
     */
    public function IsErrorResponse($response = null)
    {
        if ($response) {

            if (!empty($response->getContent())) {
                $j = $response->json();
                if (isset($j["error"])) {
                    return $j["error"];
                }
            }
            if ($response->status() != 200) {
                return "any error";
            }
            //dd($response->json());
            //dd($response->status());
        }

        if (!session()->has('errors')) return null;
        return session()->get('errors')->first();
    }

    protected function setCustomHeaders(array $headers = [])
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
        return $this;
    }

    public function test_SQLInjectionAttack()
    {
        // Попытка SQL-инъекции через поле телефона
        $maliciousPhone = "9999999862' OR '1'='1";
        $result = $this->post("/auth", ['login' => $maliciousPhone]);
        $this->assertEquals(302, $result->status());
        $this->assertStringContainsString("Количество символов", ($this->IsErrorResponse()));

        // Попытка SQL-инъекции через ID кода
        $result = $this->post("/auth/code/1' OR '1'='1", ['code' => "1111"]);
        $this->assertEquals(404, $result->status());
    }

    public function test_RaceConditionAttack()
    {
        // Создаем код верификации
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Имитируем множественные одновременные запросы
        $successCount = 0;
        for ($i = 0; $i < 5; $i++) {
            $r = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
            if (!$this->IsErrorResponse($r)) {
                $successCount++;
            }
        }

        $this->assertEquals(1, $successCount, "Race condition: множественная авторизация с одним кодом");
    }

    public function test_TimingAttack()
    {

        Cache::flush();
        PhoneVertify::where("id", ">", 0)->delete();

        // Создаем код верификации
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Замеряем время ответа для разных кодов
        $timings = [];
        for ($i = 0; $i < 4; $i++) {
            $start = microtime(true);
            $this->post("/auth/code/" . $getLastCode->id, ['code' => str_pad($i, 4, '0', STR_PAD_LEFT)]);

            $timings[] = microtime(true) - $start;
        }

        // Проверяем, что время ответа примерно одинаковое
        $avgTime = array_sum($timings) / count($timings);
        foreach ($timings as $timing) {
            $this->assertLessThan(
                $avgTime * 1.5,
                $timing,
                "Возможна уязвимость временной атаки"
            );
        }
    }

    public function RandomizeIp()
    {
        $ipAddress = rand(101, 199) . '.' . rand(111, 999) . '.' . rand(11, 99) . '.' . rand(11, 99);
        $this->serverVariables = ['REMOTE_ADDR' => $ipAddress];
        $_SERVER['REMOTE_ADDR'] = $ipAddress;
    }

    public function test_HeaderManipulationAttack()
    {
        $this->RandomizeIp();
        // Попытка подделать заголовки
        $result = $this->post("/auth", ['login' => "9999999862"])
            ->withHeaders([
                'X-Forwarded-For' => '127.0.0.1',
                'X-Original-URL' => '/admin',
                'X-Rewrite-URL' => '/admin'
            ]);

        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        $this->RandomizeIp();

        // Попытка обойти проверку IP через прокси-заголовки
        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"])
            ->withHeaders([
                'X-Forwarded-For' => '1.2.3.4',
                'Client-IP' => '1.2.3.4'
            ]);


        $this->assertEquals(302, $result->status());
        $this->assertStringContainsString("IP", $this->IsErrorResponse($result));
    }

    public function test_SessionFixationAttack()
    {
        // Попытка зафиксировать идентификатор сессии
        $this->withSession(['custom_session_id' => 'fixed_session']);

        $result = $this->post("/auth", ['login' => "9999999862"]);
        $this->assertEquals(200, $result->status());

        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Проверяем, что после успешной авторизации ID сессии изменился
        $oldSessionId = session()->getId();

        $result = $this->post("/auth/code/" . $getLastCode->id, ['code' => "1111"]);
        $this->assertEquals(302, $result->status());

        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId, "Уязвимость: ID сессии не обновляется после авторизации");
    }

    public function test_CodeLeakageProtection()
    {
        // Создаем код верификации
        $result = $this->post("/auth", ['login' => "9999999862"]);
        $getLastCode = PhoneVertify::orderByDesc("id")->first();

        // Проверяем, что код не записывается в лог-файлы
        $logContent = file_get_contents(storage_path('logs/laravel.log'));
        $this->assertFalse(
            strpos($logContent, $getLastCode->code) !== false,
            "Уязвимость: код верификации записывается в логи"
        );

        // Проверяем, что код хешируется в базе данных
        $codeFromDb = DB::table('phone_vertifies')
            ->where('id', $getLastCode->id)
            ->value('code');

        $this->assertNotEquals(
            "1111",
            $codeFromDb,
            "Уязвимость: код хранится в открытом виде"
        );
    }


    public function test_SessionRidingAttack()
    {
        //Simulate a session riding attack where the attacker tries to take advantage of session fixation vulnerabilities.

        // 1. Obtain a valid session ID (e.g., by logging in)
        $response = $this->post('/auth', ['login' => '9999999862']);
        $code = PhoneVertify::orderByDesc('id')->first()->code;
        $this->post('/auth/code/' . PhoneVertify::orderByDesc('id')->first()->id, ['code' => $code]); //Simulate successful login
        $validSessionId = session()->getId();

        // 2. Hijack session using obtained ID (This test simulates an attack. In reality, the attacker would obtain the session ID through other means)

        $this->withSession(['_token' => $validSessionId]);

        //Attempt to access a sensitive resource
        $response = $this->get('/profle'); //Replace '/admin' with the actual URL of your sensitive resource

        $this->assertEquals(404, $response->status());


    }


    public function test_CodeInferenceAttack()
    {
        $phone = "9999999862";

        // Создаем запрос на код
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());

        $code = PhoneVertify::orderByDesc("id")->first();

        // Пытаемся определить правильный код по времени ответа
        $timings = [];
        $attempts = [];

        // Делаем несколько попыток с разными кодами
        for ($i = 0; $i < 5; $i++) {
            $testCode = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $start = microtime(true);
            $this->post("/auth/code/" . $code->id, ['code' => $testCode]);
            $end = microtime(true);

            $timings[$testCode] = $end - $start;
            $attempts[] = $testCode;
        }

        // Проверяем разброс времени выполнения
        $avgTime = array_sum($timings) / count($timings);
        $maxDeviation = max(array_map(function ($time) use ($avgTime) {
            return abs($time - $avgTime);
        }, $timings));

        $this->assertLessThan(
            0.1, // максимально допустимое отклонение в секундах
            $maxDeviation,
            "Уязвимость: время проверки кода может раскрыть информацию о правильности"
        );
    }


}
