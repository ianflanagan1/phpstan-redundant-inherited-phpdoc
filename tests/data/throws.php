<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\Throws;

use RuntimeException;

final class LoadFailed extends RuntimeException
{
}

interface Loader
{
    /**
     * @throws LoadFailed
     */
    public function load(): void;
}

final class Repeats implements Loader
{
    /**
     * @throws LoadFailed
     */
    #[\Override]
    public function load(): void
    {
    }
}
