<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\ParentClassAndInterface;

abstract class Base
{
    /**
     * @param positive-int $count
     */
    public function take(int $count): void
    {
    }
}

interface Takes
{
    /**
     * @param int<0, max> $count
     */
    public function take(int $count): void;
}

final class RepeatsTheParentClass extends Base implements Takes
{
    /**
     * @param positive-int $count
     */
    #[\Override]
    public function take(int $count): void
    {
    }
}

final class RepeatsTheInterface extends Base implements Takes
{
    /**
     * @param int<0, max> $count
     */
    #[\Override]
    public function take(int $count): void
    {
    }
}
