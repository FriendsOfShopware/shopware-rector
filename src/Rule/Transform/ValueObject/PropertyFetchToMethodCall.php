<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;

final class PropertyFetchToMethodCall
{
    /**
     * @param mixed[] $newGetArguments
     */
    public function __construct(
        private string $oldType,
        private string $oldProperty,
        private string $newGetMethod,
        private ?string $newSetMethod = null,
        private array $newGetArguments = [],
    ) {
        RectorAssert::className($oldType);
        RectorAssert::propertyName($oldProperty);

        RectorAssert::methodName($newGetMethod);
        if (is_string($newSetMethod)) {
            RectorAssert::methodName($newSetMethod);
        }
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldType);
    }

    public function getOldProperty(): string
    {
        return $this->oldProperty;
    }

    public function getNewGetMethod(): string
    {
        return $this->newGetMethod;
    }

    public function getNewSetMethod(): ?string
    {
        return $this->newSetMethod;
    }

    /**
     * @return mixed[]
     */
    public function getNewGetArguments(): array
    {
        return $this->newGetArguments;
    }
}
