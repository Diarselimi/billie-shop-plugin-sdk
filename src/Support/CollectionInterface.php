<?php

declare(strict_types=1);

namespace App\Support;

use App\DomainModel\ArrayableInterface;
use Countable;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate, ArrayableInterface
{
}
