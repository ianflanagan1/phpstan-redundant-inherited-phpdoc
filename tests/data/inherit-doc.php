<?php

declare(strict_types=1);

namespace IanFlanagan\PhpstanRules\RedundantInheritedPhpdoc\Tests\data\InheritDoc;

interface Cache
{
    public function clear(): void;

    public function flush(): void;
}

final class Redis implements Cache
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function clear(): void
    {
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function flush(): void
    {
    }
}
