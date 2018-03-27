<?php

namespace App\DomainModel\Address;

use App\DomainModel\AbstractEntity;

class AddressEntity extends AbstractEntity
{
    private $country;
    private $city;
    private $postalCode;
    private $street;
    private $house_number;
    private $addition;
    private $comment;

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber()
    {
        return $this->house_number;
    }

    public function setHouseNumber($house_number)
    {
        $this->house_number = $house_number;

        return $this;
    }

    public function getAddition()
    {
        return $this->addition;
    }

    public function setAddition($addition)
    {
        $this->addition = $addition;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }
}
