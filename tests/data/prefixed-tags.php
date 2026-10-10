<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\PrefixedTags;

interface Cache
{
    /**
     * @param non-empty-string $key
     * @return list<int>
     */
    public function get(string $key): array;
}

final class Repeats implements Cache
{
    /**
     * @phpstan-param non-empty-string $key
     * @psalm-return list<int>
     */
    #[\Override]
    public function get(string $key): array
    {
        return [];
    }
}
