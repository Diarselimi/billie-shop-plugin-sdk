<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class DebtorInformationChangeRequestTransitionEntity extends AbstractStateTransitionEntity
{
    public const TRANSITION_REQUEST_CONFIRMATION = 'request_confirmation';

    public const TRANSITION_COMPLETE_MANUALLY = 'complete_manually';

    public const TRANSITION_COMPLETE_AUTOMATICALLY = 'complete_automatically';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_DECLINE_MANUALLY = 'decline_manually';

    public const ALL_TRANSITIONS = [
        self::TRANSITION_REQUEST_CONFIRMATION,
        self::TRANSITION_COMPLETE_MANUALLY,
        self::TRANSITION_COMPLETE_AUTOMATICALLY,
        self::TRANSITION_CANCEL,
        self::TRANSITION_DECLINE_MANUALLY,
    ];

    private $debtorInformationChangeRequestId;

    public function getDebtorInformationChangeRequestId(): int
    {
        return $this->debtorInformationChangeRequestId;
    }

    public function setDebtorInformationChangeRequestId(int $debtorInformationChangeRequestId): DebtorInformationChangeRequestTransitionEntity
    {
        $this->debtorInformationChangeRequestId = $debtorInformationChangeRequestId;

        return $this;
    }

    public function getReferenceId(): int
    {
        return $this->getDebtorInformationChangeRequestId();
    }

    public function setReferenceId(int $referenceId): DebtorInformationChangeRequestTransitionEntity
    {
        return $this->setDebtorInformationChangeRequestId($referenceId);
    }
}
