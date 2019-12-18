<?php


use Faker\Factory;
use Phinx\Seed\AbstractSeed;

class PhoneNumberSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $phoneNumber = $this->table('phone_number');
        $phoneNumber->truncate();

        $faker = Factory::create();
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $dateTime = $faker->dateTimeThisDecade('now', $faker->timezone);
            $timezone = $dateTime->getTimezone()->getName();
            $data[] = [
                'country_code'  => $faker->countryCode,
                'timezone'      => $timezone,
                'value'         => $faker->e164PhoneNumber,
                'first_name'    => $faker->firstName,
                'last_name'     => $faker->lastName,
                'inserted_on'   => $dateTime->format('Y-m-d H:i:s'),
                'updated_on'    => $dateTime->format('Y-m-d H:i:s'),
            ];
        }

        $phoneNumber->insert($data)->save();
    }
}
