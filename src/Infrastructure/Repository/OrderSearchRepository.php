<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\Aggregate\OrderAggregate;
use App\DomainModel\Order\Aggregate\OrderAggregateCollection;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderEntityFactory;
use App\DomainModel\Order\Search\OrderSearchQuery;
use App\DomainModel\Order\Search\OrderSearchRepositoryInterface;
use App\DomainModel\Order\Search\OrderSearchResult;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderSearchRepository extends AbstractPdoRepository implements OrderSearchRepositoryInterface
{
    private const ORDERS_COLUMNS = OrderRepository::SELECT_FIELDS;

    private const FINANCIAL_DETAILS_COLUMNS = [
        'id as order_financial_details_id',
        'order_id',
        'amount_gross',
        'amount_net',
        'amount_tax',
        'duration',
        'unshipped_amount_gross',
        'unshipped_amount_net',
        'unshipped_amount_tax',
    ];

    private OrderEntityFactory $orderFactory;

    private OrderFinancialDetailsFactory $orderFinancialDetailsFactory;

    public function __construct(
        OrderEntityFactory $orderFactory,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderFinancialDetailsFactory = $orderFinancialDetailsFactory;
    }

    public function search(OrderSearchQuery $query): OrderSearchResult
    {
        [$paginatedSql, $totalCountSql, $sqlParams] = $this->buildSql($query);

        $totalCount = $this->doFetchOne($totalCountSql, $sqlParams);
        $rows = $this->doFetchAll($paginatedSql, $sqlParams);

        return new OrderSearchResult(
            new OrderAggregateCollection(
                array_map(
                    function (array $row) {
                        return new OrderAggregate(
                            $this->orderFactory->createFromArray($row),
                            $this->orderFinancialDetailsFactory->createFromArray($row),
                            new InvoiceCollection([])
                        );
                    },
                    $rows
                )
            ),
            (int) ($totalCount['total_count'] ?? -1)
        );
    }

    private function buildSql(OrderSearchQuery $query): array
    {
        $ordersTable = OrderRepository::TABLE_NAME;
        $orderFinancialDetailsTable = OrderFinancialDetailsRepository::TABLE_NAME;

        $sql = 'SELECT %s FROM ' . $ordersTable;
        $params = [];

        if ($query->hasMerchantDebtorFilter()) {
            $sql .= " INNER JOIN merchants_debtors 
            ON {$ordersTable}.merchant_debtor_id = merchants_debtors.id 
            AND merchants_debtors.uuid = :merchant_debtor_id";
            $params['merchant_debtor_id'] = $query->getMerchantDebtorFilter();
        }

        $sql .= " INNER JOIN {$orderFinancialDetailsTable} AS ofd ON ofd.order_id = {$ordersTable}.id";
        $sql .= " INNER JOIN (SELECT MAX(id) AS max_id FROM {$orderFinancialDetailsTable} GROUP BY order_id) 
            AS ofdmax ON ofdmax.max_id = ofd.id";
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
        $selectFields .= ', ofd.' . implode(', ofd.', self::FINANCIAL_DETAILS_COLUMNS);

        return [sprintf($sql, $selectFields), $totalSql, $params];
    }
}
