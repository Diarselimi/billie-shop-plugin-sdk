<?php

namespace App\DomainModel\Person;

use App\DomainModel\AbstractEntity;

class PersonEntity extends AbstractEntity
{
    private $gender;
    private $firstName;
    private $lastName;
    private $phoneNumber;
    private $email;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): PersonEntity
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): PersonEntity
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): PersonEntity
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): PersonEntity
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): PersonEntity
    {
        $this->email = $email;

        return $this;
    }
}
