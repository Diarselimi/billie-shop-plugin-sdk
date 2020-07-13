<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\FraudRules\FraudRuleEntity;
use App\DomainModel\FraudRules\FraudRuleRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\Checker\LineItemsCheck;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\PublicDomain\PublicDomainEmailRepositoryInterface;
use App\Helper\String\StringSearch;
use App\Infrastructure\Repository\FraudRuleRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LineItemsCheckSpec extends ObjectBehavior
{
    public function let(
        FraudRuleRepository $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository
    ) {
        $this->beConstructedWith($repository, $domainEmailRepository, new StringSearch());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LineItemsCheck::class);
    }

    public function it_should_fail_because_there_is_no_lizenz_word(
        OrderContainer $orderContainer,
        FraudRuleRepositoryInterface $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        OrderLineItemEntity $lineItemEntity,
        FraudRuleEntity $ruleEntity,
        PersonEntity $entity
    ) {
        $entity->getEmail()->willReturn('diar@gmail.com');
        $ruleEntity->isCheckForPublicDomainEnabled()->willReturn(true);
        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(true);
        $orderContainer->getDebtorPerson()->willReturn($entity);
        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(true);
        $ruleEntity->getExcludedWords()->willReturn(['Lizenz']);
        $ruleEntity->getIncludedWords()->willReturn(['Download', 'ESD']);

        $lineItemEntity->getTitle()->willReturn('This is a title that contains Downloadable content');

        $orderContainer->getLineItems()->willReturn([$lineItemEntity]);
        $repository->getAll()->willReturn([$ruleEntity]);

        $this->check($orderContainer)->shouldBeAnInstanceOf(CheckResult::class);
        $this->check($orderContainer)->isPassed()->shouldReturn(false);
    }

    public function it_should_pass_if_there_are_items_that_contains_words_that_are_not_suspicious(
        OrderContainer $orderContainer,
        FraudRuleRepositoryInterface $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        OrderLineItemEntity $lineItemEntity,
        OrderLineItemEntity $secondItem,
        FraudRuleEntity $ruleEntity,
        PersonEntity $entity
    ) {
        $ruleEntity->isCheckForPublicDomainEnabled()->willReturn(true);
        $entity->getEmail()->willReturn('diar@gmail.com');
        $orderContainer->getDebtorPerson()->willReturn($entity);
        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(false);
        $ruleEntity->getExcludedWords()->willReturn(['Lizenz']);
        $ruleEntity->getIncludedWords()->willReturn(['Download', 'ESD']);

        $lineItemEntity->getTitle()->willReturn('This is a normal iphone product');
        $secondItem->getTitle()->willReturn('This is a normal blanket which u can use to dump a body somewhere');

        $orderContainer->getLineItems()->willReturn([$lineItemEntity, $secondItem]);
        $repository->getAll()->willReturn([$ruleEntity]);

        $this->check($orderContainer)->shouldBeAnInstanceOf(CheckResult::class);
        $this->check($orderContainer)->isPassed()->shouldReturn(true);
    }

    public function it_should_fail_one_of_the_product_does_not_contain_lizenz(
        OrderContainer $orderContainer,
        FraudRuleRepositoryInterface $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        OrderLineItemEntity $lineItemEntity,
        OrderLineItemEntity $secondItem,
        OrderLineItemEntity $thirdItem,
        FraudRuleEntity $ruleEntity,
        PersonEntity $entity
    ) {
        $ruleEntity->isCheckForPublicDomainEnabled()->willReturn(true);
        $entity->getEmail()->willReturn('billie@gmail.com');
        $orderContainer->getDebtorPerson()->willReturn($entity);
        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(true);

        $ruleEntity->getExcludedWords()->willReturn(['Lizenz']);
        $ruleEntity->getIncludedWords()->willReturn(['Download', 'ESD']);

        $lineItemEntity->getTitle()->willReturn('This product contains ESD content');
        $secondItem->getTitle()->willReturn('This MSOFFICE is licensed and can be used for lots of people');
        $thirdItem->getTitle()->willReturn('This product is lizenz and it can be used by all');

        $orderContainer->getLineItems()->willReturn([$lineItemEntity, $secondItem, $thirdItem]);
        $repository->getAll()->willReturn([$ruleEntity]);

        $this->check($orderContainer)->shouldBeAnInstanceOf(CheckResult::class);
        $this->check($orderContainer)->isPassed()->shouldReturn(false);
    }

    public function it_should_not_fail_if_there_is_only_lizenz_word_with_public_domain(
        OrderContainer $orderContainer,
        FraudRuleRepositoryInterface $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        OrderLineItemEntity $lineItemEntity,
        FraudRuleEntity $ruleEntity,
        PersonEntity $entity
    ) {
        $ruleEntity->isCheckForPublicDomainEnabled()->willReturn(true);
        $entity->getEmail()->willReturn('diar@gmail.com');
        $orderContainer->getDebtorPerson()->willReturn($entity);

        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(true);
        $ruleEntity->getExcludedWords()->willReturn(['Lizenz']);
        $ruleEntity->getIncludedWords()->willReturn(['Download', 'ESD']);

        $lineItemEntity->getTitle()->willReturn('This is a lizenz  product........');

        $orderContainer->getLineItems()->willReturn([$lineItemEntity]);
        $repository->getAll()->willReturn([$ruleEntity]);

        $this->check($orderContainer)->shouldBeAnInstanceOf(CheckResult::class);
        $this->check($orderContainer)->isPassed()->shouldReturn(true);
    }

    public function it_should_not_fail_if_domain_is_public_and_no_suspicious_words(
        OrderContainer $orderContainer,
        FraudRuleRepositoryInterface $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        OrderLineItemEntity $lineItemEntity,
        FraudRuleEntity $ruleEntity,
        PersonEntity $entity
    ) {
        $ruleEntity->isCheckForPublicDomainEnabled()->willReturn(true);
        $entity->getEmail()->willReturn('diar@gmail.com');
        $orderContainer->getDebtorPerson()->willReturn($entity);

        $domainEmailRepository->isKnownAsPublicDomain(Argument::any())->willReturn(true);
        $ruleEntity->getExcludedWords()->willReturn(['Lizenz']);
        $ruleEntity->getIncludedWords()->willReturn(['Download', 'ESD']);

        $lineItemEntity->getTitle()->willReturn('Iphone s8 cool :)');

        $orderContainer->getLineItems()->willReturn([$lineItemEntity]);
        $repository->getAll()->willReturn([$ruleEntity]);

        $this->check($orderContainer)->shouldBeAnInstanceOf(CheckResult::class);
        $this->check($orderContainer)->isPassed()->shouldReturn(true);
    }
}
