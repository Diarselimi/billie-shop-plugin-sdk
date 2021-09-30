<?php

namespace spec\App\Application\UseCase\ShipOrderWithInvoice;

use App\Application\UseCase\CreateInvoice\CreateInvoiceRequest;
use App\Application\UseCase\ShipOrderWithInvoice\ShipOrderWithInvoiceRequest;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\Lifecycle\ShipOrder\LegacyShipOrderService;
use App\DomainModel\Order\Lifecycle\ShipOrder\ShipOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use App\Tests\Helpers\TestUuidGenerator;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class ShipOrderWithInvoiceUseCaseSpec extends ObjectBehavior
{
    private $testFile;

    public function let(
        InvoiceDocumentCreator $invoiceManager,
        OrderContainerFactory $orderContainerFactory,
        LegacyShipOrderService $legacyShipOrderService,
        ShipOrderService $shipOrderService,
        Registry $workflowRegistry,
        LegacyOrderResponseFactory $orderResponseFactory,
        InvoiceFactory $invoiceFactory,
        ValidatorInterface $validator
    ) {
        $args = func_get_args();
        $validator = array_pop($args);
        $args[] = new TestUuidGenerator();
        $args[] = $validator;

        $this->beConstructedWith(...$args);

        $this->testFile = tempnam(sys_get_temp_dir(), 'upl');

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList([]));
        $this->setValidator($validator);
    }

    public function letGo()
    {
        unlink($this->testFile);
    }

    public function it_ships_v2_order(
        OrderContainerFactory $orderContainerFactory,
        OrderContainer $orderContainer,
        Registry $workflowRegistry,
        Workflow $workflow,
        InvoiceFactory $invoiceFactory,
        ShipOrderService $shipOrderService,
        InvoiceDocumentCreator $invoiceManager,
        LegacyOrderResponseFactory $orderResponseFactory
    ): void {
        $orderId = 1;
        $merchantId = 1;
        $uploadedFile = new UploadedFile(
            $this->testFile,
            'test.txt'
        );

        $request = (new ShipOrderWithInvoiceRequest($orderId, $merchantId))
            ->setAmount(new TaxedMoney(
                new Money(500),
                new Money(450),
                new Money(50)
            ))
            ->setInvoiceNumber(123)
            ->setInvoiceFile($uploadedFile);

        $invoice = (new Invoice())
            ->setUuid(Uuid::uuid4()->toString());

        $input = new CreateInvoiceRequest($merchantId, Uuid::uuid4());
        $input->setAmount($request->getAmount())
            ->setExternalCode($request->getInvoiceNumber());

        $invoiceFactory->create(
            $orderContainer,
            Argument::any()
        )->willReturn($invoice);

        $order = (new OrderEntity())
            ->setId($orderId)
            ->setWorkflowName(OrderEntity::WORKFLOW_NAME_V2)
            ->setMerchantId($merchantId);
        $orderContainer->getOrder()->willReturn($order);
        $orderContainerFactory->loadByMerchantIdAndUuid(
            $request->getOrderId(),
            $request->getMerchantId()
        )->willReturn($orderContainer);
        $workflowRegistry->get($order)->willReturn($workflow);
        $workflow->can($order, Argument::type('string'))->willReturn(true);
        $duration = 30;
        $financialDetails = (new OrderFinancialDetailsEntity())
            ->setUnshippedAmountGross(new Money(1000))
            ->setUnshippedAmountNet(new Money(800))
            ->setUnshippedAmountTax(new Money(200))
            ->setDuration($duration);
        $orderContainer->getOrderFinancialDetails()->willReturn($financialDetails);

        $orderResponseFactory->create($orderContainer);
        $invoiceManager->createFromUpload(Argument::cetera());

        $orderContainer->addInvoice($invoice)->shouldBeCalledOnce();
        $shipOrderService->ship($orderContainer, $invoice)->shouldBeCalledOnce();

        $this->execute($request);
    }
}
