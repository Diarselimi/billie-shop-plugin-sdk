<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\PublicDomain\PublicDomainEmailRepositoryInterface;
use App\Helper\String\StringSearch;
use App\Infrastructure\Repository\FraudRuleRepository;

class LineItemsCheck implements CheckInterface
{
    public const NAME = 'line_items';

    private $rulesRepository;

    private $stringHelper;

    private $domainEmailRepository;

    public function __construct(
        FraudRuleRepository $repository,
        PublicDomainEmailRepositoryInterface $domainEmailRepository,
        StringSearch $stringHelper
    ) {
        $this->rulesRepository = $repository;
        $this->stringHelper = $stringHelper;
        $this->domainEmailRepository = $domainEmailRepository;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $orderLineItems = $orderContainer->getLineItems();
        $rules = $this->rulesRepository->getAll();

        $emailDomain = ltrim(strstr($orderContainer->getDebtorPerson()->getEmail(), '@'), '@');
        $isEmailPublicDomain = false;

        foreach ($orderLineItems as $lineItem) {
            foreach ($rules as $rule) {
                if ($rule->isCheckForPublicDomainEnabled()) {
                    $isEmailPublicDomain = $this->domainEmailRepository->isKnownAsPublicDomain($emailDomain);
                }

                $breaksTheRule = $this->isBreakingTheRule(
                    $rule->getExcludedWords(),
                    $rule->getIncludedWords(),
                    $lineItem->getTitle()
                );

                if ($breaksTheRule && $isEmailPublicDomain) {
                    return new CheckResult(false, self::NAME);
                }
            }
        }

        return new CheckResult(true, self::NAME);
    }

    private function isBreakingTheRule(array $excludedWords, array $includedWords, string $text): bool
    {
        $includedWordsFound = $this->stringHelper->isAnyWordsInString($includedWords, $text);
        $excludedWordsFound = $this->stringHelper->isAnyWordsInString($excludedWords, $text);

        return $includedWordsFound && !$excludedWordsFound;
    }
}
