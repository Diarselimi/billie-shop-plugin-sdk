<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Person\PersonEntity;
use App\DomainModel\Person\PersonEntityFactory;
use App\DomainModel\Person\PersonRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class PersonRepository extends AbstractPdoRepository implements PersonRepositoryInterface
{
    private const SELECT_FIELDS = 'id, gender, first_name, last_name, phone, email, created_at, updated_at';

    private $factory;

    public function __construct(PersonEntityFactory $factory)
    {
        $this->factory = $factory;
    }

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
            'created_at' => $person->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $person->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $person->setId($id);
    }

    public function getOneById(int $id): ? PersonEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM persons
          WHERE id = :id
        ', [
            'id' => $id,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
