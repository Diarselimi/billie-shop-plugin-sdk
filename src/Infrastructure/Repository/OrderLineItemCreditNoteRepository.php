<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderLineItem\OrderLineItemCreditNoteEntity;
use App\DomainModel\OrderLineItem\OrderLineItemCreditNoteRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderLineItemCreditNoteRepository extends AbstractPdoRepository implements OrderLineItemCreditNoteRepositoryInterface
{
    public const TABLE_NAME = 'order_line_item_credit_notes';

    public const SELECT_FIELDS = [
        'id',
        'credit_note_uuid',
        'order_line_item_id',
    ];

    public function create(OrderLineItemCreditNoteEntity $entity): void
    {
        $data = [
            'credit_note_uuid' => $entity->getCreditNoteUuid(),
            'order_line_item_id' => $entity->getOrderLineItemId(),
        ];
        $sql = $this->generateInsertQuery(self::TABLE_NAME, array_keys($data));
        $id = $this->doInsert($sql, $data);
        $entity->setId($id);
    }
}
