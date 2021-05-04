<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Validator\Constraint;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\Validator\Constraint\UpdateAmount;
use App\Application\Validator\Constraint\UpdateAmountValidator;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateAmountValidatorTest extends UnitTestCase
{
    use RandomDataTrait;
    use FakeDataFiller;

    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepo;

    private UpdateAmountValidator $updateAmountValidator;

    public function setUp(): void
    {
        $this->financialDetailsRepo = $this->getMockBuilder(OrderFinancialDetailsRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateAmountValidator = new UpdateAmountValidator($this->financialDetailsRepo);
    }

    /** @test */
    public function shouldAddViolationWhenAmountsAreNotValid()
    {
        $value = 'not_important';
        $request = new UpdateOrderRequest('some_random_uuid', 1, null, TaxedMoneyFactory::create(123, 120, 3));

        $this->financialDetailsRepo->expects($this->once())
            ->method('findOneByOrderUuid')
            ->willReturn($this->getRandomFinantialDetails(100));

        $executionContextMock = $this->getMockBuilder(ExecutionContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $executionContextMock->expects($this->once())
            ->method('getRoot')
            ->willReturn($request);

        $executionContextMock
            ->expects($this->once())
            ->method('addViolation')
            ->willReturn(null);

        $constraintMock = $this->getMockBuilder(UpdateAmount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateAmountValidator->initialize($executionContextMock);
        $this->updateAmountValidator->validate($value, $constraintMock);
    }

    public function shouldNotAddViolationWhenAmountsAreTheSame()
    {
        $value = 'not_important';
        $request = new UpdateOrderRequest('some_random_uuid', 1, null, TaxedMoneyFactory::create(123, 120, 3));

        $this->financialDetailsRepo->expects($this->once())
            ->method('findOneByOrderUuid')
            ->willReturn($this->getRandomFinantialDetails(123));

        $executionContextMock = $this->getMockBuilder(ExecutionContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $executionContextMock->expects($this->once())
            ->method('getRoot')
            ->willReturn($request);

        $executionContextMock
            ->expects($this->never())
            ->method('addViolation')
            ->willReturn(null);

        $constraintMock = $this->getMockBuilder(UpdateAmount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateAmountValidator->initialize($executionContextMock);
        $this->updateAmountValidator->validate($value, $constraintMock);
    }
}
