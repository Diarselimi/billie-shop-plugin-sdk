<?php

declare(strict_types=1);

namespace App\Support;

use App\DomainModel\ArrayableInterface;
use Countable;
use IteratorAggregate;

/**
 * @deprecated
 * @see \Ozean12\Support\Collections\CollectionInterface
 */
interface CollectionInterface extends Countable, IteratorAggregate, ArrayableInterface
{
}
