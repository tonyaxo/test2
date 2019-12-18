<?php declare(strict_types=1);

namespace Bogatyrev;

/**
 * PhoneNumberFactory
 */
class PhoneNumberFactory
{
    /**
     * Creates instance from db data array.
     *
     * @param array $data
     * @return PhoneNumber
     */
    public function createFromDb(array $data): PhoneNumber
    {
        $pn = new PhoneNumber();
        $pn->setId($data['id']);
        $pn->setFirstName($data['first_name']);
        $pn->setLastName($data['last_name']);
        $pn->setTimezone($data['timezone']);
        $pn->setCountryCode($data['country_code']);
        $pn->setValue($data['value']);
        $pn->setInsertedOn($data['inserted_on']);
        $pn->setUpdatedOn($data['updated_on']);
        return $pn;
    }

    /**
     * Creates instance from json string. 
     *
     * @param string $json
     * @return PhoneNumber
     */
    public function createFromJson(string $json): PhoneNumber
    {
        $data = \json_decode($json, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Error('Json decode error');
        }

        return $this->createFromArray($data);
    }

    /**
     * Creates instance from array format.
     *
     * @param array $data
     * @return PhoneNumber
     */
    public function createFromArray(array $data): PhoneNumber
    {
        $pn = new PhoneNumber();
        $pn->setFirstName($data['firstName'] ?? '');
        $pn->setLastName($data['lastName'] ?? '');
        $pn->setTimezone($data['timezone'] ?? '');
        $pn->setCountryCode($data['countryCode'] ?? '');
        $pn->setValue($data['value'] ?? '');
        $pn->setInsertedOn($data['insertedOn'] ?? null);
        $pn->setUpdatedOn($data['updatedOn'] ?? null);
        $pn->updated();
        
        return $pn;
    }
}
