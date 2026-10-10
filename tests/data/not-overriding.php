<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\NotOverriding;

abstract class Base
{
    /**
     * @param positive-int $count
     */
    public function __construct(int $count)
    {
    }

    /**
     * @param positive-int $count
     */
    private function hidden(int $count): void
    {
    }
}

final class Child extends Base
{
    /**
     * Builds the child
     *
     * @param positive-int $count
     */
    public function __construct(int $count)
    {
        parent::__construct($count);
    }

    /**
     * Not an override, since the parent's is private
     *
     * @param positive-int $count
     */
    private function hidden(int $count): void
    {
    }

    /**
     * Its own method
     *
     * @return list<int>
     */
    public function ids(): array
    {
        return [];
    }
}

interface Cache
{
    public function clear(): void;
}

final class OtherTags implements Cache
{
    /**
     * @deprecated
     * @phpstan-impure
     */
    #[\Override]
    public function clear(): void
    {
    }
}
