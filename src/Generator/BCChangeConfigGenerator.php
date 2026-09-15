<?php

declare(strict_types=1);

namespace Frosh\Rector\Generator;

use Frosh\Rector\Rule\BCChange\FutureCompatibleBCChangeRector;

final class BCChangeConfigGenerator
{
    private const DEFAULT_ATTRIBUTE_NAMESPACE = 'Shopware\Core\Framework\Deprecation\BCChange\\';

    public function __construct(private readonly string $attributeNamespace = self::DEFAULT_ATTRIBUTE_NAMESPACE) {}

    /**
     * @param iterable<class-string> $classes
     *
     * @return list<array<string, mixed>>
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

        usort($changes, static fn (array $left, array $right): int => [
            $left['class'],
            $left['method'],
            $left['kind'],
            $left['parameter'] ?? '',
        ] <=> [
            $right['class'],
            $right['method'],
            $right['kind'],
            $right['parameter'] ?? '',
        ]);

        return $changes;
    }

    /**
     * @param list<array<string, mixed>> $changes
     */
    public function render(array $changes): string
    {
        $configuration = $this->exportArray($changes, 2);

        return <<<PHP
            <?php

            declare(strict_types=1);

            use Frosh\\Rector\\Rule\\BCChange\\FutureCompatibleBCChangeRector;
            use Rector\\Config\\RectorConfig;

            return static function (RectorConfig \$rectorConfig): void {
                \$rectorConfig->ruleWithConfiguration(
                    FutureCompatibleBCChangeRector::class,
                    {$configuration},
                );
            };

            PHP;
    }

    /**
     * @param array<int|string, mixed> $arguments
     *
     * @return array<string, mixed>|null
     */
    private function change(string $class, \ReflectionMethod $method, string $attribute, array $arguments): ?array
    {
        $change = [
            'class' => $class,
            'method' => $method->getName(),
        ];

        if ($attribute === $this->attributeNamespace . 'NewOptionalParameter') {
            return $change + [
                'kind' => FutureCompatibleBCChangeRector::ADD_OPTIONAL_PARAMETER,
                'position' => count($method->getParameters()),
                'parameter' => $this->stringArgument($arguments, 'parameterName', 1),
                'type' => $this->stringArgument($arguments, 'parameterType', 2),
                'default' => $arguments['defaultValue'] ?? $arguments[3] ?? null,
            ];
        }

        if ($attribute === $this->attributeNamespace . 'ParameterTypeWidening') {
            $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
            $parameter = $this->parameter($method, $parameterName);

            return $change + [
                'kind' => FutureCompatibleBCChangeRector::WIDEN_PARAMETER_TYPE,
                'parameter' => $parameterName,
                'currentType' => $parameter->getType() === null ? null : (string) $parameter->getType(),
                'type' => $this->stringArgument($arguments, 'newType', 2),
            ];
        }

        if ($attribute === $this->attributeNamespace . 'ReturnTypeNarrowing') {
            return $change + [
                'kind' => FutureCompatibleBCChangeRector::NARROW_RETURN_TYPE,
                'currentType' => $method->getReturnType() === null ? null : (string) $method->getReturnType(),
                'type' => $this->stringArgument($arguments, 'newType', 1),
            ];
        }

        if ($attribute !== $this->attributeNamespace . 'ParameterDefaultValueChange') {
            return null;
        }

        $parameterName = $this->stringArgument($arguments, 'parameterName', 1);
        $parameter = $this->parameter($method, $parameterName);

        if (!$parameter->isDefaultValueAvailable()) {
            throw new \RuntimeException(sprintf('Cannot resolve the current default of %s::%s($%s).', $class, $method->getName(), $parameterName));
        }

        return $change + [
            'kind' => FutureCompatibleBCChangeRector::EXPLICIT_CURRENT_DEFAULT,
            'position' => $parameter->getPosition(),
            'parameter' => $parameterName,
            'default' => $parameter->getDefaultValue(),
        ];
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

            $lines[] = $prefix . (is_array($value) ? $this->exportArray($value, $depth + 1) : $this->exportValue($value)) . ',';
        }

        $lines[] = str_repeat(' ', $depth * 4) . ']';

        return implode("\n", $lines);
    }

    private function exportValue(mixed $value): string
    {
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
