<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\Search\OrderSearchQuery;
use App\DomainModel\Order\Search\OrderSearchRepositoryInterface;
use App\DomainModel\Order\Search\OrderSearchResult;
use App\Infrastructure\Repository\Order\PdoOrderEntityFactory;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderSearchRepository extends AbstractPdoRepository implements OrderSearchRepositoryInterface
{
    private const ORDERS_COLUMNS = OrderPdoRepository::SELECT_FIELDS;

    private PdoOrderEntityFactory $orderFactory;

    public function __construct(PdoOrderEntityFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    public function search(OrderSearchQuery $query): OrderSearchResult
    {
        [$paginatedSql, $totalCountSql, $sqlParams] = $this->buildSql($query);

        $totalCount = $this->doFetchOne($totalCountSql, $sqlParams);
        $rows = $this->doFetchAll($paginatedSql, $sqlParams);

        return new OrderSearchResult(
            $this->orderFactory->createCollection($rows),
            (int) ($totalCount['total_count'] ?? -1)
        );
    }

    private function buildSql(OrderSearchQuery $query): array
    {
        $ordersTable = OrderPdoRepository::TABLE_NAME;

        $sql = 'SELECT %s FROM ' . $ordersTable;
        $params = [];

        if ($query->hasMerchantDebtorFilter()) {
            $sql .= " INNER JOIN merchants_debtors 
            ON {$ordersTable}.merchant_debtor_id = merchants_debtors.id 
            AND merchants_debtors.uuid = :merchant_debtor_id";
            $params['merchant_debtor_id'] = $query->getMerchantDebtorFilter();
        }

        $sql .= " WHERE {$ordersTable}.merchant_id = :merchant_id AND state != :state_new";

        $params['merchant_id'] = $query->getMerchantId();
        $params['state_new'] = OrderEntity::STATE_NEW;

        if ($query->hasSearchString()) {
            $sql .= " AND ({$ordersTable}.external_code LIKE :search 
                OR {$ordersTable}.uuid LIKE :search OR {$ordersTable}.invoice_number LIKE :search )";

            $params['search'] = '%' . $query->getSearchString() . '%';
        }

        if ($query->hasStateFilter()) {
            $states = array_map(
                static fn ($state) => "'{$state}'",
                $query->getStateFilter()
            );

            $sql .= " AND {$ordersTable}.state IN (" . implode(',', $states) . ')';
        }

        $totalSql = sprintf($sql, 'COUNT(*) as total_count');
        $sql .= " ORDER BY {$query->getSortBy()} {$query->getSortDirection()} 
            LIMIT {$query->getOffset()},{$query->getLimit()}";

        $selectFields = "{$ordersTable}." . implode(", {$ordersTable}.", self::ORDERS_COLUMNS);

        return [sprintf($sql, $selectFields), $totalSql, $params];
    }
}
