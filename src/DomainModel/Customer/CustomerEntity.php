<?php

namespace App\DomainModel\Customer;

use App\DomainModel\AbstractEntity;

class CustomerEntity extends AbstractEntity
{
    private $name;
    private $apiKey;
    private $roles;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CustomerEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): CustomerEntity
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getRoles(): string
    {
        return $this->roles;
    }

    public function setRoles(string $roles): CustomerEntity
    {
        $this->roles = $roles;

        return $this;
    }
}
