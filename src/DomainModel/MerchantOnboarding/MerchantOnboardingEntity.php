<?php

namespace App\DomainModel\MerchantOnboarding;

use App\DomainModel\MerchantIdEntityTrait;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityTrait;
use Billie\PdoBundle\DomainModel\UuidEntityTrait;

class MerchantOnboardingEntity extends AbstractTimestampableEntity implements StatefulEntityInterface
{
    use UuidEntityTrait;
    use MerchantIdEntityTrait;
    use StatefulEntityTrait;

    public const STATE_NEW = 'new';

    public const STATE_COMPLETE = 'complete';

    public const STATE_CANCELED = 'canceled';

    public const INITIAL_STATE = self::STATE_NEW;

    public const ALL_STATES = [self::STATE_NEW, self::STATE_COMPLETE, self::STATE_CANCELED];

    private const STATE_TRANSITION_ENTITY_CLASS = MerchantOnboardingTransitionEntity::class;

    public function getStateTransitionEntityClass(): string
    {
        return self::STATE_TRANSITION_ENTITY_CLASS;
    }
}
