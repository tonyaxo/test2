<?php declare(strict_types=1);

use Bogatyrev\PhoneNumberFactory;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * @dataProvider phoneNumberDataProvider
     */
    public function testValidate($item, $result)
    {
        $factory = new PhoneNumberFactory();
        $p = $factory->createFromArray($item);
        $p->setId($item['id'] ?? null);
        
        $this->assertSame($result, $p->validate());
    }

    public function phoneNumberDataProvider(): array
    {
        return [
            'valid' => [
                'item' => [
                    'id' => 111,
                    'firstName' => 'First Name',
                    'lastName' => '',
                    'timezone' => 'America/Los_Angeles',
                    'countryCode' => 'US',
                    'value' => '+27113456789',
                    'insertedOn' => '1991-11-10 15:10:00',
                    'updatedOn' => '1991-11-10 15:10:00',
                ],
                'result' => true,
            ],
            'invalid timezone' => [
                'item' => [
                    'id' => 111,
                    'firstName' => 'First Name',
                    'lastName' => 'Last Name',
                    'timezone' => 'America/Unknown',
                    'countryCode' => 'US',
                    'value' => '+27113456789',
                    'insertedOn' => '1991-11-10 15:10:00',
                    'updatedOn' => '1991-11-10 15:10:00',
                ],
                'result' => false,
            ],
            'invalid countryCode' => [
                'item' => [
                    'id' => 111,
                    'firstName' => 'First Name',
                    'lastName' => 'Last Name',
                    'timezone' => 'America/Los_Angeles',
                    'countryCode' => 'TTT',
                    'value' => '+27113456789',
                    'insertedOn' => '1991-11-10 15:10:00',
                    'updatedOn' => '1991-11-10 15:10:00',
                ],
                'result' => false,
            ],
        ];
    }
}
