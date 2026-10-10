<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\TraitMethod;

interface Cache
{
    /**
     * @return list<int>
     */
    public function ids(): array;
}

trait ListsIds
{
    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return [];
    }
}

final class Redis implements Cache
{
    use ListsIds;
}
