<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PhpParser\ParserFactory;

final class NativeTypeParser
{
    public static function parse(string $type): Identifier|Name|ComplexType
    {
        $statement = (new ParserFactory())->createForNewestSupportedVersion()->parse(sprintf('<?php function value(): %s {}', $type))[0] ?? null;
        if (!$statement instanceof Function_ || $statement->returnType === null) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a native PHP type.', $type));
        }

        return self::qualify($statement->returnType);
    }

    private static function qualify(Identifier|Name|ComplexType $type): Identifier|Name|ComplexType
    {
        if ($type instanceof Identifier || $type instanceof Name) {
            return self::qualifyAtomic($type);
        }

        if ($type instanceof NullableType) {
            return new NullableType(self::qualifyAtomic($type->type));
        }

        if ($type instanceof UnionType) {
            return new UnionType(array_map(
                static fn (Identifier|Name|IntersectionType $member): Identifier|Name|IntersectionType => $member instanceof IntersectionType
                    ? self::qualifyIntersection($member)
                    : self::qualifyAtomic($member),
                $type->types,
            ));
        }

        if (!$type instanceof IntersectionType) {
            throw new \LogicException(sprintf('Unsupported native type node "%s".', $type::class));
        }

        return self::qualifyIntersection($type);
    }

    private static function qualifyAtomic(Identifier|Name $type): Identifier|Name
    {
        if (!$type instanceof Name || in_array(strtolower($type->toString()), ['self', 'static', 'parent'], true)) {
            return $type;
        }

        return new FullyQualified($type->toString());
    }

    private static function qualifyIntersection(IntersectionType $type): IntersectionType
    {
        return new IntersectionType(array_map(self::qualifyAtomic(...), $type->types));
    }
}
