<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\Param;

interface Cache
{
    /**
     * @param non-empty-string $key
     */
    public function get(string $key, mixed $default = null): mixed;
}

final class Repeats implements Cache
{
    /**
     * @param non-empty-string $key
     * @param mixed $default
     */
    #[\Override]
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

final class RenamesTheParameter implements Cache
{
    /**
     * @param non-empty-string $name
     */
    #[\Override]
    public function get(string $name, mixed $default = null): mixed
    {
        return $default;
    }
}
