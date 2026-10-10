<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\NativeParentType;

interface Named
{
    public function rename(string $name): string;
}

final class Repeats implements Named
{
    /**
     * @param string $name
     * @return string
     */
    #[\Override]
    public function rename(string $name): string
    {
        return $name;
    }
}
