<?php

declare(strict_types=1);

namespace Frosh\Rector\Generator;

use Frosh\Rector\Rule\BCChange\BCChangeConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\AddOptionalParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\AddRequiredParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChange;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterType;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeReturnType;
use Frosh\Rector\Rule\BCChange\ValueObject\RemoveParameter;

final readonly class BCChangeConfigGenerator
{
    private const DEFAULT_ATTRIBUTE_NAMESPACE = 'Shopware\Core\Framework\Deprecation\BCChange\\';

    public function __construct(private string $attributeNamespace = self::DEFAULT_ATTRIBUTE_NAMESPACE) {}

    /**
     * @param iterable<class-string> $classes
     *
     * @return list<BCChange>
     */
    public function collect(iterable $classes, string $version): array
    {
        $changes = [];

        foreach ($classes as $class) {
            $reflection = new \ReflectionClass($class);

            foreach ($reflection->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                    continue;
                }

                foreach ($method->getAttributes() as $attribute) {
                    $arguments = $attribute->getArguments();
                    if (($arguments['version'] ?? $arguments[0] ?? null) !== $version) {
                        continue;
                    }

                    $change = $this->change($reflection->getName(), $method, $attribute->getName(), $arguments);
                    if ($change !== null) {
                        $changes[] = $change;
                    }
                }
            }
        }

        $this->sort($changes);

        return $changes;
    }

    /**
     * @param list<BCChange> $changes
     */
    public function render(array $changes): string
    {
        $configuration = $this->exportValue(new BCChangeConfiguration($changes), 0);

        return <<<PHP
            <?php

            declare(strict_types=1);

            return {$configuration};

            PHP;
    }

    /**
     * @param list<BCChange> $existingChanges
     * @param list<BCChange> $newChanges
     *
     * @return list<BCChange>
     */
    public function replaceVersion(array $existingChanges, array $newChanges, string $version): array
    {
        $changes = array_values(array_filter(
            $existingChanges,
            static fn (BCChange $change): bool => $change->version() !== $version,
        ));

        $changes = array_merge($changes, $newChanges);
        $this->sort($changes);

        return $changes;
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    private function change(string $class, \ReflectionMethod $method, string $attribute, array $arguments): ?BCChange
    {
        $version = $this->stringArgument($arguments, 'version', 0);

        if ($attribute === $this->attributeNamespace . 'NewOptionalParameter') {
            return new AddOptionalParameter(
                $version,
                $class,
                $method->getName(),
                count($method->getParameters()),
                $this->stringArgument($arguments, 'parameterName', 1),
                $this->stringArgument($arguments, 'parameterType', 2),
                $arguments['defaultValue'] ?? $arguments[3] ?? null,
            );
        }

        if ($attribute === $this->attributeNamespace . 'ParameterTypeWidening') {
            $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
            $parameter = $this->parameter($method, $parameterName);

            return new ChangeParameterType(
                $version,
                $class,
                $method->getName(),
                $parameterName,
                $parameter->getType() === null ? null : (string) $parameter->getType(),
                $this->stringArgument($arguments, 'newType', 2),
            );
        }

        if ($attribute === $this->attributeNamespace . 'ReturnTypeNarrowing') {
            return new ChangeReturnType(
                $version,
                $class,
                $method->getName(),
                $method->getReturnType() === null ? null : (string) $method->getReturnType(),
                $this->stringArgument($arguments, 'newType', 1),
            );
        }

        if ($attribute === $this->attributeNamespace . 'ParameterNameChange') {
            $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
            $parameter = $this->parameter($method, $parameterName);

            return new ChangeParameterName(
                $version,
                $class,
                $method->getName(),
                $parameter->getPosition(),
                $parameterName,
                $this->stringArgument($arguments, 'newName', 2),
                $this->parametersBefore($method, $parameter->getPosition()),
            );
        }

        if ($attribute === $this->attributeNamespace . 'ParameterRemoval') {
            $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
            $parameter = $this->parameter($method, $parameterName);

            return new RemoveParameter($version, $class, $method->getName(), $parameter->getPosition(), $parameterName);
        }

        if ($attribute === $this->attributeNamespace . 'NewRequiredParameter') {
            return new AddRequiredParameter(
                $version,
                $class,
                $method->getName(),
                count($method->getParameters()),
                $this->stringArgument($arguments, 'parameterName', 1),
                $this->stringArgument($arguments, 'parameterType', 2),
            );
        }

        if ($attribute !== $this->attributeNamespace . 'ParameterDefaultValueChange') {
            return null;
        }

        $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
        $parameter = $this->parameter($method, $parameterName);

        if (!$parameter->isDefaultValueAvailable()) {
            throw new \RuntimeException(sprintf('Cannot resolve the current default of %s::%s($%s).', $class, $method->getName(), $parameterName));
        }

        return new ChangeParameterDefault(
            $version,
            $class,
            $method->getName(),
            $parameter->getPosition(),
            $parameterName,
            $parameter->getDefaultValue(),
        );
    }

    /** @return list<array{name: string, hasDefault: bool, default?: mixed}> */
    private function parametersBefore(\ReflectionMethod $method, int $position): array
    {
        $parameters = [];

        foreach (array_slice($method->getParameters(), 0, $position) as $parameter) {
            $item = [
                'name' => $parameter->getName(),
                'hasDefault' => $parameter->isDefaultValueAvailable(),
            ];
            if ($parameter->isDefaultValueAvailable()) {
                $item['default'] = $parameter->getDefaultValue();
            }

            $parameters[] = $item;
        }

        return $parameters;
    }

    /** @param array<int|string, mixed> $arguments */
    private function stringArgument(array $arguments, string $name, int $position): string
    {
        $value = $arguments[$name] ?? $arguments[$position] ?? null;
        if (!is_string($value)) {
            throw new \RuntimeException(sprintf('BC-change attribute argument "%s" must be a string.', $name));
        }

        return $value;
    }

    private function parameter(\ReflectionMethod $method, string $parameterName): \ReflectionParameter
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getName() === $parameterName) {
                return $parameter;
            }
        }

        throw new \RuntimeException(sprintf('Cannot resolve parameter %s::%s($%s).', $method->getDeclaringClass()->getName(), $method->getName(), $parameterName));
    }

    /** @param list<BCChange> $changes */
    private function sort(array &$changes): void
    {
        usort($changes, static function (BCChange $left, BCChange $right): int {
            $leftConfiguration = $left->configuration();
            $rightConfiguration = $right->configuration();

            return [
                $left->version(),
                $leftConfiguration['class'],
                $leftConfiguration['method'],
                $left::class,
                $leftConfiguration['parameter'] ?? '',
            ] <=> [
                $right->version(),
                $rightConfiguration['class'],
                $rightConfiguration['method'],
                $right::class,
                $rightConfiguration['parameter'] ?? '',
            ];
        });
    }

    /** @param array<array-key, mixed> $values */
    private function exportArray(array $values, int $depth): string
    {
        if ($values === []) {
            return '[]';
        }

        $lines = ['['];
        $list = array_is_list($values);

        foreach ($values as $key => $value) {
            $prefix = str_repeat(' ', ($depth + 1) * 4);
            if (!$list) {
                $prefix .= var_export($key, true) . ' => ';
            }

            $lines[] = $prefix . $this->exportValue($value, $depth + 1) . ',';
        }

        $lines[] = str_repeat(' ', $depth * 4) . ']';

        return implode("\n", $lines);
    }

    private function exportValue(mixed $value, int $depth): string
    {
        if (is_array($value)) {
            return $this->exportArray($value, $depth);
        }
        if (is_object($value)) {
            return $this->exportObject($value, $depth);
        }
        if ($value === null) {
            return 'null';
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }

        return is_string($value) ? $this->exportString($value) : var_export($value, true);
    }

    private function exportObject(object $value, int $depth): string
    {
        $reflection = new \ReflectionClass($value);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            throw new \LogicException(sprintf('Cannot export %s without a constructor.', $value::class));
        }

        $lines = ['new ' . $value::class . '('];
        foreach ($constructor->getParameters() as $parameter) {
            $property = $reflection->getProperty($parameter->getName());
            $lines[] = sprintf(
                '%s%s: %s,',
                str_repeat(' ', ($depth + 1) * 4),
                $parameter->getName(),
                $this->exportValue($property->getValue($value), $depth + 1),
            );
        }
        $lines[] = str_repeat(' ', $depth * 4) . ')';

        return implode("\n", $lines);
    }

    private function exportString(string $value): string
    {
        $escaped = '';
        $length = strlen($value);

        for ($position = 0; $position < $length; $position++) {
            $character = $value[$position];
            if ($character === "'") {
                $escaped .= "\\'";
            } elseif ($character === '\\' && ($position + 1 === $length || in_array($value[$position + 1], ['\\', "'"], true))) {
                $escaped .= '\\\\';
            } else {
                $escaped .= $character;
            }
        }

        return "'{$escaped}'";
    }
}
