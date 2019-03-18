<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class PersonRepository extends AbstractPdoRepository implements PersonRepositoryInterface
{
    public function insert(PersonEntity $person): void
    {
        $id = $this->doInsert('
            INSERT INTO persons
            (gender, first_name, last_name, phone, email, created_at, updated_at)
            VALUES
            (:gender, :first_name, :last_name, :phone, :email, :created_at, :updated_at)
        ', [
            'gender' => $person->getGender(),
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'phone' => $person->getPhoneNumber(),
            'email' => $person->getEmail(),
            'created_at' => $person->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $person->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $person->setId($id);
    }

    public function getOneById(int $id): ? PersonEntity
    {
        return (new PersonEntity())
            ->setId(43)
        ;
    }

    public function getOneByIdRaw(int $id): ? array
    {
        $address = $this->doFetchOne('SELECT * FROM addresses WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }
}
