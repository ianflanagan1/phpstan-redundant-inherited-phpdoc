<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\Templates;

use DateTimeImmutable;

/**
 * @template T
 */
interface Repository
{
    /**
     * @return T
     */
    public function find(int $id): mixed;
}

/**
 * @implements Repository<DateTimeImmutable>
 */
final class Dates implements Repository
{
    /**
     * @return DateTimeImmutable
     */
    #[\Override]
    public function find(int $id): mixed
    {
        return new DateTimeImmutable();
    }
}
