<?php


namespace SlavaWins\AuthSms\IntegrationTests;

//use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Tests\TestCase;

class DeBruteAuthTest extends TestCase
{




    public  function IsErrorResponse()
    {
        if (!session()->has('errors')) return null;
        return session()->get('errors')->first();
    }



    public function RandomizeIp()
    {
        $ipAddress =   rand(101, 199) . '.' . rand(111, 999) . '.' . rand(11, 99) . '.' . rand(11, 99);
        $this->serverVariables = ['REMOTE_ADDR' => $ipAddress];
        $_SERVER['REMOTE_ADDR'] = $ipAddress;
    }


    public function test_ABrutePhoneAuth()
    {

        $this->RandomizeIp();


        Cache::flush();

        config(["authsms.AttemptsMaxByIp" => '3']);

        putenv('AUTHSMS_TEST_AttemptsMaxByIp=2');
        $phone = "9999999861";


        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());


        for ($i = 0; $i <= 4; $i++) {
            $this->post("/auth", ['login' => $phone]);
        }

        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(302, $result->status());


        $this->RandomizeIp();

        $phone = "9999999862";
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());
    }


    public function test_GlobalBrutePhoneGuard()
    {
        $this->RandomizeIp();

        config(["authsms.AttemptsMaxByIp" => '3']);

        $phone = "9999999861";

        $limitKey = "globalbrute__" . $phone;
        RateLimiter::clear($limitKey);
        Cache::flush();


        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());


        for ($i = 0; $i <= 15; $i++) {
            $this->RandomizeIp();
            $this->post("/auth", ['login' => $phone]);
        }

        $this->RandomizeIp();
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(302, $result->status());


        $phone = "9999999862";
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());
    }


    public function test_GlobalBruteForAll()
    {
        $this->RandomizeIp();


        $phone = "9999999861";
        Cache::flush();


        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());


        for ($i = 0; $i <= 40; $i++) {
            $this->RandomizeIp();

            $phone = "999999".rand(1111, 9999);
            $this->post("/auth", ['login' => $phone]);
        }

        $this->RandomizeIp();
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(302, $result->status());


        RateLimiter::clear("globalbrute___all");
        $result = $this->post("/auth", ['login' => $phone]);
        $this->assertEquals(200, $result->status());
    }

}
