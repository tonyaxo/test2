<?php declare(strict_types=1);

use Bogatyrev\validators\TimezoneRule;
use PHPUnit\Framework\TestCase;

class TimezoneRuleTest extends TestCase
{
    /**
     * @dataProvider timezoneDataProvider
     */
    public function testValidateTrue($timezone, $result)
    {
        $v = new TimezoneRule();
        
        $this->assertSame($result, $v->validate($timezone));
    }

    public function timezoneDataProvider(): array
    {
        return [
            [
                'timezone' => 'America/Los_Angeles',
                'result' => true,
            ],
            [
                'timezone' => 'UNKNOWN',
                'result' => false,
            ]
        ];
    }
}
