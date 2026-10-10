<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\ReturnTag;

abstract class Finder
{
    /**
     * @return list<int>
     */
    abstract public function ids(): array;
}

final class Repeats extends Finder
{
    /**
     * @return list<int>
     */
    #[\Override]
    public function ids(): array
    {
        return [];
    }
}
