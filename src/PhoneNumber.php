<?php declare(strict_types=1);

namespace Bogatyrev;

use Bogatyrev\validators\CountryRule;
use Bogatyrev\validators\TimezoneRule;
use DateTime;
use DateTimeZone;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

/**
 * Phone number item.
 */
class PhoneNumber
{
    /**
     * ID
     *
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $insertedOn;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $updatedOn;

    /**
     * Get iD
     *
     * @return  integer
     */ 
    public function getId(): ?int
    {
        return $this->id === null ? null : (int) $this->id;
    }

    /**
     * Set iD
     *
     * @param  integer  $id  ID
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of firstName
     *
     * @return  string
     */ 
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @param  string  $firstName
     *
     * @return  self
     */ 
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of lastName
     *
     * @return  string
     */ 
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @param  string  $lastName
     *
     * @return  self
     */ 
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of countryCode
     *
     * @return  string
     */ 
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set the value of countryCode
     *
     * @param  string  $countryCode
     *
     * @return  self
     */ 
    public function setCountryCode(string $countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get the value of value
     *
     * @return  string
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param  string  $value
     *
     * @return  self
     */ 
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of timezone
     *
     * @return string
     */ 
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set the value of timezone
     *
     * @param  DateTimeZone  $timezone
     *
     * @return  self
     */ 
    public function setTimezone(string $timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Returns DateTimeZone object.
     *
     * @return DateTimeZone|null
     */
    public function getTimezoneTz(): ?DateTimeZone
    {
        return new DateTimeZone($this->timezone);
    }

    /**
     * Get the value of insertedOn
     *
     * @return  string
     */ 
    public function getInsertedOn()
    {
        return $this->insertedOn;
    }

    /**
     * Set the value of insertedOn
     *
     * @param  string  $insertedOn
     *
     * @return  self
     */ 
    public function setInsertedOn(string $insertedOn)
    {
        $this->insertedOn = $insertedOn;

        return $this;
    }

    public function getInsertedOnDt(): ?DateTime
    {
        $result = DateTime::createFromFormat('Y-m-d H:i:s', $this->getInsertedOn(), $this->getTimezoneTz());
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */ 
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * Set undocumented variable
     *
     * @param  string $updatedOn  Undocumented variable
     *
     * @return  self
     */ 
    public function setUpdatedOn(string $updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * Returns as DateTime object.
     *
     * @return DateTime|null
     */
    public function getUpdatedOnDt(): ?DateTime
    {
        $result = DateTime::createFromFormat('Y-m-d H:i:s', $this->getUpdatedOn(), $this->getTimezoneTz());
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * Returns as array data.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'timezone' => $this->getTimezone(),
            'countryCode' => $this->getCountryCode(),
            'value' => $this->getValue(),
            'insertedOn' => $this->getInsertedOn(),
            'updatedOn' => $this->getUpdatedOn(),
        ];
    }

    /**
     * Validate current attributes.
     *
     * @return boolean
     */
    public function validate(): bool
    {
        try {
            return v::phone()                           ->assert($this->getValue())
                && v::stringType()                      ->assert($this->getFirstName())
                && v::optional(v::intVal())             ->assert($this->getId())
                && v::optional(v::stringType())         ->assert($this->getLastName())
                && v::optional(new CountryRule())       ->assert($this->getCountryCode())
                && v::optional(new TimezoneRule())      ->assert($this->getTimezone())
                && v::optional(v::date('Y-m-d H:i:s'))  ->assert($this->getInsertedOn())
                && v::optional(v::date('Y-m-d H:i:s'))  ->assert($this->getUpdatedOn());
        } catch (NestedValidationException $e) {
            return false;
        }
    }

    /**
     * @return self
     */
    public function updated(): self
    {
        $date = (new DateTime())->format('Y-m-d H:i:s');
        if ($this->getInsertedOn() === null) {
            $this->setInsertedOn($date);
        }
        $this->setUpdatedOn($date);

        return $this;
    }
}
