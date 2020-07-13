<?php

namespace spec\App\Application\UseCase\GetMerchantDebtors;

use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsRequest;
use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsUseCase;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntityFactory;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsDTO;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsDTOFactory;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsRepositoryInterface;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorContainerFactory;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorList;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use App\Support\PaginatedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetMerchantDebtorsUseCaseSpec extends ObjectBehavior
{
    public function let(
        SearchMerchantDebtorsRepositoryInterface $searchMerchantDebtorRepository,
        MerchantDebtorContainerFactory $merchantDebtorContainerFactory,
        MerchantDebtorResponseFactory $responseFactory,
        SearchMerchantDebtorsDTOFactory $searchMerchantDebtorFactory,
        MerchantDebtorEntityFactory $entityFactory,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GetMerchantDebtorsUseCase::class);
    }

    public function it_returns_the_merchant_debtors_list(
        GetMerchantDebtorsRequest $request,
        SearchMerchantDebtorsRepositoryInterface $searchMerchantDebtorRepository,
        MerchantDebtorResponseFactory $responseFactory,
        SearchMerchantDebtorsDTOFactory $searchMerchantDebtorFactory
    ) {
        $searchMerchantDebtorFactory
            ->create($request)
            ->willReturn(new SearchMerchantDebtorsDTO());

        $searchMerchantDebtorRepository
            ->searchMerchantDebtors(new SearchMerchantDebtorsDTO())
            ->willReturn(new PaginatedCollection());

        $merchantDebtorList = new MerchantDebtorList();
        $responseFactory
            ->createList(0, [])
            ->willReturn($merchantDebtorList);

        $this->execute($request)->shouldBe($merchantDebtorList);
    }
}
