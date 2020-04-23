<?php

namespace spec\App\Support;

use App\Support\PaginationFilterInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use Webmozart\Assert\Assert;

class PaginatedCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            ['keep_after_filter' => false],
            ['keep_after_filter' => true],
        ]);
    }

    public function it_resets_the_keys_after_filter()
    {
        // Arrange
        $filter = new class implements PaginationFilterInterface {
            public function check(array $item): bool
            {
                return $item['keep_after_filter'] === true;
            }
        };

        // Act
        /** @var Subject $subject */
        $subject = $this->filter($filter);

        // Assert
        Assert::eq(
            json_encode($subject->getWrappedObject()->getItems()),
            '[{"keep_after_filter":true}]'
        );
    }
}
