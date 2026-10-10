<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\DifferentTypes;

use LogicException;
use RuntimeException;

interface Cache
{
    /**
     * @param non-empty-string $key
     * @return list<int>
     * @throws RuntimeException
     */
    public function get(string $key): array;
}

final class Differs implements Cache
{
    /**
     * @param string $key
     * @param positive-int $limit
     * @return list<positive-int>
     * @throws LogicException
     */
    #[\Override]
    public function get(string $key, int $limit = 1): array
    {
        return [];
    }
}
