<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\Description;

interface Cache
{
    /**
     * @return list<int>
     */
    public function ids(): array;
}

final class Summarised implements Cache
{
    /**
     * Returns the ids from Redis
     */
    #[\Override]
    public function ids(): array
    {
        return [];
    }
}

final class TagDescription implements Cache
{
    /**
     * @return list<positive-int> only ids that have been saved
     */
    #[\Override]
    public function ids(): array
    {
        return [];
    }
}
