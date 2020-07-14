<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck;

use App\DomainModel\CollectionInterface;
use ArrayIterator;
use Traversable;

class CheckResultCollection implements CollectionInterface
{
    /**
     * @var CheckResult[]
     */
    private $elements;

    public function __construct(CheckResult ...$checkResults)
    {
        $this->elements = [];
        foreach ($checkResults as $result) {
            $this->elements[$result->getName()] = $result;
        }
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function getFirstHardDeclined(): ?CheckResult
    {
        foreach ($this->elements as $result) {
            if ($result->isDeclineOnFailure() && !$result->isPassed()) {
                return $result;
            }
        }

        return null;
    }

    public function getFirstSoftDeclined(): ?CheckResult
    {
        foreach ($this->elements as $result) {
            if (!$result->isDeclineOnFailure() && !$result->isPassed()) {
                return $result;
            }
        }

        return null;
    }

    public function getFirstDeclined(): ?CheckResult
    {
        foreach ($this->elements as $element) {
            if (!$element->isPassed()) {
                return $element;
            }
        }

        return null;
    }

    public function getAllDeclined(): array
    {
        return array_values(
            array_filter($this->elements, function (CheckResult $result) {
                return !$result->isPassed();
            })
        );
    }

    public function add(CheckResult $result): self
    {
        $this->elements[$result->getName()] = $result;

        return $this;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}
