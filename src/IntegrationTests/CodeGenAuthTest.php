<?php


//use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;
use SlavaWins\AuthSms\Models\PhoneVertify;
use Tests\TestCase;

class CodeGenAuthTest extends TestCase
{



    public function test_CodeLen()
    {

        $code = PhoneVertify::GetRandomCode();
        $this->assertEquals(4, strlen($code));

        $code = PhoneVertify::GetRandomCode();
        $phonevertify = PhoneVertify::MakeTryByPhone("9999999999", request()->ip(), $code);

        $this->assertNotEquals($code, $phonevertify->code);




        $this->assertTrue($phonevertify->IsCodeEqals($code));

        $this->assertFalse($phonevertify->IsCodeEqals($code.'1'));


    }
}
