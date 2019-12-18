<?php declare(strict_types=1);

use Bogatyrev\validators\CountryRule;
use PHPUnit\Framework\TestCase;

class CountryRuleTest extends TestCase
{
    /**
     * @dataProvider countryDataProvider
     */
    public function testValidateTrue($country, $result)
    {
        $v = new CountryRule();
        
        $this->assertSame($result, $v->validate($country));
    }

    public function countryDataProvider(): array
    {
        return [
            [
                'country' => 'RU',
                'result' => true,
            ],
            [
                'country' => 'UNKNOWN',
                'result' => false,
            ]
        ];
    }
}
