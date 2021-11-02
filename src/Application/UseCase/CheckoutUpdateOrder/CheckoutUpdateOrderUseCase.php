<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutUpdateOrder;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepository;
use App\Infrastructure\Repository\OrderFinancialDetailsRepository;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CheckoutUpdateOrderUseCase implements ValidatedUseCaseInterface, LoggingInterface, CommandHandler
{
    use ValidatedUseCaseTrait, LoggingTrait;

    private OrderContainerFactory $orderContainerFactory;

    private CompaniesServiceInterface $companiesService;

    private AddressRepositoryInterface $addressRepository;

    private DebtorExternalDataRepositoryInterface $debtorExternalDataRepository;

    private OrderRepository $orderRepository;

    private OrderFinancialDetailsRepository $financialDetailsRepository;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        CompaniesServiceInterface $companiesService,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderRepository $orderRepository,
        OrderFinancialDetailsRepository $financialDetailsRepository
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->companiesService = $companiesService;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->orderRepository = $orderRepository;
        $this->financialDetailsRepository = $financialDetailsRepository;
    }

    public function execute(CheckoutUpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(
                $request->getSessionUuid()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($request->getBillingAddress() !== null) {
            $this->saveAndAssociateBillingAddressToOrder($request, $orderContainer);
        }

        if ($request->getDuration() !== null) {
            $this->updateDurationForOrder($orderContainer, $request->getDuration());
        }
    }

    private function associateBillingAddressWithIdentifiedCompany(
        OrderContainer $orderContainer,
        AddressEntity $billingAddress
    ) {
        $identificationBillingAddressUuid = $this->companiesService->updateCompanyBillingAddress(
            $orderContainer->getDebtorCompany()->getUuid(),
            $billingAddress
        );

        $this->orderRepository->updateIdentificationBillingAddress(
            $orderContainer->getOrder()->getId(),
            $identificationBillingAddressUuid->toString()
        );
    }

    private function associateBillingAddressWithExternalData(
        OrderContainer $orderContainer,
        AddressEntity $billingAddress
    ) {
        $this->addressRepository->insert($billingAddress);

        $externalData = $orderContainer->getDebtorExternalData();
        $externalData->setBillingAddressId($billingAddress->getId());
        $this->debtorExternalDataRepository->update($externalData);
    }

    private function updateDurationForOrder(OrderContainer $orderContainer, int $newDuration): void
    {
        $orderFinancialDetailsEntity = clone $orderContainer->getOrderFinancialDetails();
        $currentDuration = $orderFinancialDetailsEntity->getDuration();

        if ($newDuration === $currentDuration) {
            return;
        }

        if ($newDuration < $currentDuration) {
            throw RequestValidationException::createForInvalidValue(
                'New duration cannot be lower than the original one',
                'duration',
                $newDuration
            );
        }

        $orderFinancialDetailsEntity
            ->setDuration($newDuration)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->financialDetailsRepository->insert($orderFinancialDetailsEntity);
        $orderContainer->setOrderFinancialDetails($orderFinancialDetailsEntity);

        $durationExtension = $newDuration - $currentDuration;

        $this->orderRepository->updateDurationExtension(
            $orderContainer->getOrder()->getId(),
            $durationExtension
        );
    }

    private function saveAndAssociateBillingAddressToOrder(
        CheckoutUpdateOrderRequest $request,
        OrderContainer $orderContainer
    ) {
        $billingAddress = (new AddressEntity())
            ->setStreet($request->getBillingAddress()->getStreet())
            ->setHouseNumber($request->getBillingAddress()->getHouseNumber())
            ->setAddition($request->getBillingAddress()->getAddition())
            ->setPostalCode($request->getBillingAddress()->getPostalCode())
            ->setCity($request->getBillingAddress()->getCity())
            ->setCountry($request->getBillingAddress()->getCountry());

        $this->associateBillingAddressWithIdentifiedCompany($orderContainer, $billingAddress);
        $this->associateBillingAddressWithExternalData($orderContainer, $billingAddress);
    }
}
