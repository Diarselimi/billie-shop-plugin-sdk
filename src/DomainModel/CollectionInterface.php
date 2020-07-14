<?php

declare(strict_types=1);

namespace App\DomainModel;

use Countable;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate, ArrayableInterface
{
}
