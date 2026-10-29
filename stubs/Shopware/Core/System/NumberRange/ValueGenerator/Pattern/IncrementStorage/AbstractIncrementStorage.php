<?php declare(strict_types=1);

namespace Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage;

abstract class AbstractIncrementStorage
{
    public function getDecorated(): self {}
}
