<?php

namespace App\DomainModel\MerchantOnboarding;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityTrait;
use Billie\PdoBundle\DomainModel\UuidEntityTrait;

class MerchantOnboardingStepEntity extends AbstractTimestampableEntity implements StatefulEntityInterface
{
    use UuidEntityTrait;
    use StatefulEntityTrait;

    public const STATE_NEW = 'new';

    public const STATE_PENDING = 'confirmation_pending';

    public const STATE_COMPLETE = 'complete';

    public const INITIAL_STATE = self::STATE_NEW;

    public const ALL_STATES = [self::STATE_NEW, self::STATE_PENDING, self::STATE_COMPLETE];

    public const STEP_FINANCIAL_ASSESSMENT = 'financial_assessment';

    public const STEP_SIGNATORY_CONFIRMATION = 'signatory_confirmation';

    public const STEP_IDENTITY_VERIFICATION = 'identity_verification';

    public const STEP_UBO_PEPSANCTIONS_ASSESSMENT = 'ubo_pepsanctions_assessment';

    public const STEP_TECHNICAL_INTEGRATION = 'technical_integration';

    public const STEP_SEPA_MANDATE_CONFIRMATION = 'sepa_mandate_confirmation';

    public const ALL_STEPS = [
        self::STEP_FINANCIAL_ASSESSMENT,
        self::STEP_SIGNATORY_CONFIRMATION,
        self::STEP_IDENTITY_VERIFICATION,
        self::STEP_UBO_PEPSANCTIONS_ASSESSMENT,
        self::STEP_TECHNICAL_INTEGRATION,
        self::STEP_SEPA_MANDATE_CONFIRMATION,
    ];

    public const ALL_PUBLIC_STEPS = [
        self::STEP_FINANCIAL_ASSESSMENT,
        self::STEP_SIGNATORY_CONFIRMATION,
        self::STEP_IDENTITY_VERIFICATION,
        self::STEP_TECHNICAL_INTEGRATION,
        self::STEP_SEPA_MANDATE_CONFIRMATION,
    ];

    public const ALL_INTERNAL_STEPS = [
        self::STEP_UBO_PEPSANCTIONS_ASSESSMENT,
    ];

    private const STATE_TRANSITION_ENTITY_CLASS = MerchantOnboardingStepTransitionEntity::class;

    private $merchantOnboardingId;

    private $name;

    private $isInternal;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MerchantOnboardingStepEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getMerchantOnboardingId(): int
    {
        return $this->merchantOnboardingId;
    }

    public function setMerchantOnboardingId(int $merchantOnboardingId): MerchantOnboardingStepEntity
    {
        $this->merchantOnboardingId = $merchantOnboardingId;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->isInternal;
    }

    public function setIsInternal(bool $isInternal): MerchantOnboardingStepEntity
    {
        $this->isInternal = $isInternal;

        return $this;
    }

    public function getStateTransitionEntityClass(): string
    {
        return self::STATE_TRANSITION_ENTITY_CLASS;
    }
}
