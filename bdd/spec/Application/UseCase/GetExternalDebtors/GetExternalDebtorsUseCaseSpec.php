<?php

namespace spec\App\Application\UseCase\GetExternalDebtors;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsResponse;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsRequest;
use App\Application\UseCase\GetExternalDebtors\GetExternalDebtorsUseCase;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetExternalDebtorsUseCaseSpec extends ObjectBehavior
{
    public function let(
        CompaniesServiceInterface $companiesService,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GetExternalDebtorsUseCase::class);
    }

    public function it_searches_for_external_debtors(
        ValidatorInterface $validator,
        GetExternalDebtorsRequest $request,
        CompaniesServiceInterface $companiesService
    ) {
        $request->getSearchString()->willReturn('gmbh');
        $request->getLimit()->willReturn(5);
        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());

        $companiesService
            ->searchExternalDebtors('gmbh', 5)
            ->shouldBeCalledOnce()
            ->willReturn([]);

        $response = $this->execute($request);
        $response->shouldBeAnInstanceOf(GetExternalDebtorsResponse::class);
    }

    public function it_throws_exception_if_search_param_is_empty(
        ValidatorInterface $validator,
        GetExternalDebtorsRequest $request,
        ConstraintViolation $violation
    ) {
        $request->getSearchString()->willReturn('');
        $validator->validate($request, Argument::any(), Argument::any())->shouldBeCalledOnce()->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $this->shouldThrow(RequestValidationException::class)->during('execute', [$request]);
    }
}
