<?php

declare(strict_types=1);

namespace spec\App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtorExternalIds\GetMerchantDebtorExternalIdsRequest;
use App\Application\UseCase\GetMerchantDebtorExternalIds\GetMerchantDebtorExternalIdsUseCase;
use App\Http\HttpConstantsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMerchantDebtorExternalIdsControllerSpec extends ObjectBehavior
{
    public function let(GetMerchantDebtorExternalIdsUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_return_list_when_external_ids_found(GetMerchantDebtorExternalIdsUseCase $useCase): void
    {
        $merchantId = 1;
        $uuid = Uuid::uuid4()->toString();
        $request = Request::create('');
        $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $merchantId);

        $externalIds = ['some-external-id'];
        $useCase->execute(Argument::that(function (GetMerchantDebtorExternalIdsRequest $useCaseRequest) use (
            $uuid,
            $merchantId
        ) {
            return $uuid === $useCaseRequest->getMerchantDebtorUuid()
                && $merchantId === $useCaseRequest->getMerchantId();
        }))->willReturn($externalIds);

        $externalIdsList = $this->execute($uuid, $request);
        $externalIdsList->getTotal()->shouldBe(1);
        $externalIdsList->getItems()->shouldBe($externalIds);
    }

    public function it_should_throw_exception_when_not_found(GetMerchantDebtorExternalIdsUseCase $useCase): void
    {
        $useCase->execute(Argument::any())->willThrow(MerchantDebtorNotFoundException::class);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('execute', [Uuid::uuid4()->toString(), Request::create('')]);
    }
}
