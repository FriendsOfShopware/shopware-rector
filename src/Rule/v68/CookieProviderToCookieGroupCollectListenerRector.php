<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Migrates decorators of the deprecated CookieProviderInterface, which only append static cookie groups with array_merge,
 * to a listener of the CookieGroupCollectEvent, available since Shopware 6.7.3.0.
 * The service definition still decorates the interface afterwards, it has to be registered as event listener instead,
 * `shopware-cli extension fix` rewires migrated services automatically.
 */
final class CookieProviderToCookieGroupCollectListenerRector extends AbstractRector
{
    private const COOKIE_PROVIDER_INTERFACE = 'Shopware\Storefront\Framework\Cookie\CookieProviderInterface';

    private const EVENT = 'Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent';

    private const COOKIE_GROUP = 'Shopware\Core\Content\Cookie\Struct\CookieGroup';

    private const COOKIE_ENTRY = 'Shopware\Core\Content\Cookie\Struct\CookieEntry';

    private const COOKIE_ENTRY_COLLECTION = 'Shopware\Core\Content\Cookie\Struct\CookieEntryCollection';

    private const GROUP_KEYS = ['snippet_name', 'snippet_description', 'cookie', 'value', 'expiration', 'isRequired', 'entries'];

    private const ENTRY_KEYS = ['snippet_name', 'snippet_description', 'cookie', 'value', 'expiration', 'hidden'];

    /**
     * Legacy array keys mapped to the CookieGroup and CookieEntry properties and their scalar types.
     */
    private const PROPERTIES = [
        'snippet_name' => ['name', 'string'],
        'snippet_description' => ['description', 'string'],
        'value' => ['value', 'string'],
        'expiration' => ['expiration', 'int'],
        'isRequired' => ['isRequired', 'bool'],
        'hidden' => ['hidden', 'bool'],
    ];

    /**
     * @var array<string, Name>
     */
    private array $names = [];

    /**
     * @var array<string, string>
     */
    private array $pendingImports = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate static CookieProviderInterface decorators to CookieGroupCollectEvent listeners', [new CodeSample(
            <<<'CODE_SAMPLE'
                use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

                class CustomCookieProvider implements CookieProviderInterface
                {
                    public function __construct(private readonly CookieProviderInterface $inner)
                    {
                    }

                    public function getCookieGroups(): array
                    {
                        return array_merge($this->inner->getCookieGroups(), [[
                            'snippet_name' => 'cookie.name',
                            'cookie' => 'my-cookie',
                            'value' => '1',
                            'expiration' => '30',
                        ]]);
                    }
                }
                CODE_SAMPLE,
            <<<'CODE_SAMPLE'
                use Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent;
                use Shopware\Core\Content\Cookie\Struct\CookieGroup;

                class CustomCookieProvider
                {
                    public function __invoke(CookieGroupCollectEvent $event): void
                    {
                        if (!$event->cookieGroupCollection->has('cookie.name')) {
                            $cookieGroup = new CookieGroup('cookie.name');
                            $cookieGroup->setCookie('my-cookie');
                            $cookieGroup->value = '1';
                            $cookieGroup->expiration = 30;
                            $event->cookieGroupCollection->add($cookieGroup);
                        }
                    }
                }
                CODE_SAMPLE,
        )]);
    }

    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /** @param FileNode $node */
    public function refactor(Node $node): ?Node
    {
        $changed = false;

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                $changed = $this->refactorScope($stmt->stmts) || $changed;
            }
        }

        $changed = $this->refactorScope($node->stmts) || $changed;

        return $changed ? $node : null;
    }

    /**
     * @param array<Stmt> $stmts
     */
    private function refactorScope(array &$stmts): bool
    {
        $changed = false;

        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Class_) {
                continue;
            }

            $this->prepareNames($stmts);
            if ($this->refactorClass($stmt)) {
                $changed = true;
                $this->updateImports($stmts);
            }
        }

        return $changed;
    }

    private function refactorClass(Class_ $class): bool
    {
        $interfaceKey = null;
        foreach ($class->implements as $key => $interface) {
            if ($this->isName($interface, self::COOKIE_PROVIDER_INTERFACE)) {
                $interfaceKey = $key;
            }
        }

        if ($interfaceKey === null || $class->extends !== null || $class->isAbstract() || $class->getMethod('__invoke') !== null) {
            return false;
        }

        $method = $class->getMethod('getCookieGroups');
        if ($method === null || $method->stmts === null || count($method->stmts) !== 1 || !$method->stmts[0] instanceof Return_) {
            return false;
        }

        $merge = $method->stmts[0]->expr;
        if (!$merge instanceof FuncCall || !$this->isName($merge, 'array_merge') || $merge->isFirstClassCallable() || count($merge->getArgs()) !== 2) {
            return false;
        }

        [$innerArg, $groupsArg] = $merge->getArgs();
        if ($innerArg->unpack || $groupsArg->unpack || $innerArg->name !== null || $groupsArg->name !== null) {
            return false;
        }

        $innerProperty = $this->resolveInnerProperty($innerArg->value);
        if ($innerProperty === null) {
            return false;
        }

        $inlinedConstants = [];
        $groups = $this->parseGroups($class, $groupsArg->value, $inlinedConstants);
        if ($groups === null || !$this->removeInnerProvider($class, $innerProperty)) {
            return false;
        }

        unset($class->implements[$interfaceKey]);
        $class->implements = array_values($class->implements);

        $this->createListener($method, $groups);

        $this->removeUnusedConstants($class, $inlinedConstants);

        return true;
    }

    private function resolveInnerProperty(Expr $expr): ?string
    {
        if (!$expr instanceof MethodCall || !$this->isName($expr->name, 'getCookieGroups') || $expr->args !== []) {
            return null;
        }

        $fetch = $expr->var;
        if (!$fetch instanceof PropertyFetch || !$fetch->var instanceof Variable || !$this->isName($fetch->var, 'this') || !$fetch->name instanceof Identifier) {
            return null;
        }

        return $fetch->name->toString();
    }

    /**
     * Removes the inner provider from the constructor, the property, and the property assignment.
     * Returns false and leaves the class untouched when the inner provider is used anywhere else.
     */
    private function removeInnerProvider(Class_ $class, string $property): bool
    {
        $constructor = $class->getMethod('__construct');
        if ($constructor === null || $constructor->stmts === null) {
            return false;
        }

        $nodeFinder = new NodeFinder();
        $fetches = $nodeFinder->find($class->stmts, fn (Node $node): bool => $node instanceof PropertyFetch
            && $node->var instanceof Variable && $this->isName($node->var, 'this') && $this->isName($node->name, $property));
        $selfCalls = $nodeFinder->find($class->stmts, fn (Node $node): bool => $node instanceof MethodCall
            && $node->var instanceof Variable && $this->isName($node->var, 'this') && $this->isName($node->name, 'getCookieGroups'));
        if ($selfCalls !== []) {
            return false;
        }

        foreach ($constructor->params as $key => $param) {
            if ($param->isPromoted() && $this->isName($param->var, $property)) {
                if (count($fetches) !== 1 || !$this->isCookieProviderType($param)) {
                    return false;
                }

                unset($constructor->params[$key]);
                $constructor->params = array_values($constructor->params);
                $this->removeEmptyConstructor($class, $constructor);

                return true;
            }
        }

        // The inner provider is assigned to a declared property: `$this->property = $param;`
        $propertyKey = null;
        foreach ($class->stmts as $key => $stmt) {
            if ($stmt instanceof Property && count($stmt->props) === 1 && $this->isName($stmt->props[0], $property)) {
                $propertyKey = $key;
            }
        }

        if ($propertyKey === null || count($fetches) !== 2) {
            return false;
        }

        foreach ($constructor->stmts as $stmtKey => $stmt) {
            if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign || !in_array($stmt->expr->var, $fetches, true) || !$stmt->expr->expr instanceof Variable) {
                continue;
            }

            $variable = $this->getName($stmt->expr->expr);
            foreach ($constructor->params as $paramKey => $param) {
                if ($param->isPromoted() || !$this->isName($param->var, (string) $variable) || !$this->isCookieProviderType($param)) {
                    continue;
                }

                $usages = $nodeFinder->find($constructor->stmts, fn (Node $node): bool => $node instanceof Variable && $this->isName($node, (string) $variable));
                if (count($usages) !== 1) {
                    return false;
                }

                unset($constructor->stmts[$stmtKey], $constructor->params[$paramKey], $class->stmts[$propertyKey]);
                $constructor->stmts = array_values($constructor->stmts);
                $constructor->params = array_values($constructor->params);
                $class->stmts = array_values($class->stmts);
                $this->removeEmptyConstructor($class, $constructor);

                return true;
            }
        }

        return false;
    }

    private function isCookieProviderType(Param $param): bool
    {
        return $param->type instanceof Name && $this->isName($param->type, self::COOKIE_PROVIDER_INTERFACE);
    }

    private function removeEmptyConstructor(Class_ $class, ClassMethod $constructor): void
    {
        if ($constructor->params !== [] || $constructor->stmts !== []) {
            return;
        }

        foreach ($class->stmts as $key => $stmt) {
            if ($stmt === $constructor) {
                unset($class->stmts[$key]);
            }
        }

        $class->stmts = array_values($class->stmts);
    }

    /**
     * @param array<string, true> $inlinedConstants
     *
     * @return list<array{properties: array<string, Expr>, cookie: ?Expr, entries: ?list<array<string, Expr>>}>|null
     */
    private function parseGroups(Class_ $class, Expr $expr, array &$inlinedConstants): ?array
    {
        $groups = $this->resolveArray($class, $expr, $inlinedConstants);
        if ($groups === null) {
            return null;
        }

        $parsed = [];
        foreach ($groups->items as $item) {
            if ($item->key !== null || $item->unpack || $item->byRef) {
                return null;
            }

            $group = $this->resolveArray($class, $item->value, $inlinedConstants);
            $values = $group === null ? null : $this->parseValues($group, self::GROUP_KEYS);
            if ($values === null || !isset($values['snippet_name'])) {
                return null;
            }

            $entries = null;
            if (isset($values['entries'])) {
                $entries = $this->parseEntries($class, $values['entries'], $inlinedConstants);
                // A group holds either entries or a standalone cookie.
                if ($entries === null || isset($values['cookie'])) {
                    return null;
                }
            }

            $cookie = null;
            if (isset($values['cookie'])) {
                $cookie = $this->convert($values['cookie'], 'string');
                if (!$cookie instanceof Expr) {
                    return null;
                }
            } elseif ($entries === null) {
                return null;
            }

            $properties = $this->convertProperties($values);
            if ($properties === null || !isset($properties['name'])) {
                return null;
            }

            $parsed[] = ['properties' => $properties, 'cookie' => $cookie, 'entries' => $entries];
        }

        return $parsed;
    }

    /**
     * @param array<string, true> $inlinedConstants
     *
     * @return list<array<string, Expr>>|null
     */
    private function parseEntries(Class_ $class, Expr $expr, array &$inlinedConstants): ?array
    {
        $entries = $this->resolveArray($class, $expr, $inlinedConstants);
        if ($entries === null) {
            return null;
        }

        $parsed = [];
        foreach ($entries->items as $item) {
            if ($item->unpack || $item->byRef) {
                return null;
            }

            $entry = $this->resolveArray($class, $item->value, $inlinedConstants);
            $values = $entry === null ? null : $this->parseValues($entry, self::ENTRY_KEYS);
            if ($values === null || !isset($values['cookie'])) {
                return null;
            }

            $cookie = $this->convert($values['cookie'], 'string');
            $properties = $this->convertProperties($values);
            if (!$cookie instanceof Expr || $properties === null) {
                return null;
            }

            $parsed[] = ['cookie' => $cookie] + $properties;
        }

        return $parsed;
    }

    /**
     * @param list<string> $allowedKeys
     *
     * @return array<string, Expr>|null
     */
    private function parseValues(Array_ $array, array $allowedKeys): ?array
    {
        $values = [];
        foreach ($array->items as $item) {
            if (!$item->key instanceof String_ || $item->unpack || $item->byRef || !in_array($item->key->value, $allowedKeys, true)) {
                return null;
            }

            $values[$item->key->value] = $item->value;
        }

        return $values;
    }

    /**
     * @param array<string, Expr> $values
     *
     * @return array<string, Expr>|null
     */
    private function convertProperties(array $values): ?array
    {
        $properties = [];
        foreach (self::PROPERTIES as $key => [$property, $type]) {
            if (!isset($values[$key])) {
                continue;
            }

            $converted = $this->convert($values[$key], $type);
            if ($converted === false) {
                return null;
            }

            if ($converted !== null) {
                $properties[$property] = $converted;
            }
        }

        return $properties;
    }

    /**
     * Converts a legacy array value like the legacy cookie provider bridge of Shopware does.
     * Returns null when the bridge ignores the value, and false when the value cannot be converted safely.
     */
    private function convert(Expr $expr, string $type): Expr|false|null
    {
        if ($expr instanceof ConstFetch) {
            $value = strtolower($this->getName($expr) ?? '');
            if ($value === 'null') {
                return null;
            }

            if ($value !== 'true' && $value !== 'false') {
                return false;
            }

            return match ($type) {
                'string' => $value === 'true' ? new String_('1') : null,
                'int' => new Int_($value === 'true' ? 1 : 0),
                default => new ConstFetch(new Name($value)),
            };
        }

        if ($expr instanceof String_ || $expr instanceof Int_) {
            $value = $expr->value;

            return match ($type) {
                'string' => (string) $value === '' ? null : new String_((string) $value),
                'int' => is_numeric($value) && (string) (int) $value === trim((string) $value) ? new Int_((int) $value) : false,
                default => new ConstFetch(new Name((bool) $value ? 'true' : 'false')),
            };
        }

        $exprType = $this->getType($expr);
        $matches = match ($type) {
            'string' => $exprType->isNonEmptyString()->yes(),
            'int' => $exprType->isInteger()->yes(),
            default => $exprType->isBoolean()->yes(),
        };

        return $matches ? $this->deepClone($expr) : false;
    }

    /**
     * @param array<string, true> $inlinedConstants
     */
    private function resolveArray(Class_ $class, Expr $expr, array &$inlinedConstants): ?Array_
    {
        if ($expr instanceof Array_) {
            return $expr;
        }

        if (!$expr instanceof ClassConstFetch || !$expr->class instanceof Name || !$expr->name instanceof Identifier) {
            return null;
        }

        $className = $expr->class->toLowerString();
        if ($className !== 'self' && $className !== 'static' && !$this->isName($expr->class, (string) $this->getName($class))) {
            return null;
        }

        foreach ($class->getConstants() as $classConst) {
            foreach ($classConst->consts as $const) {
                if ($const->name->toString() !== $expr->name->toString()) {
                    continue;
                }

                $inlinedConstants[$const->name->toString()] = true;

                return $this->resolveArray($class, $const->value, $inlinedConstants);
            }
        }

        return null;
    }

    /**
     * @param array<string, true> $constants
     */
    private function removeUnusedConstants(Class_ $class, array $constants): void
    {
        $nodeFinder = new NodeFinder();

        foreach ($constants as $name => $_) {
            $usages = $nodeFinder->find($class->stmts, fn (Node $node): bool => $node instanceof ClassConstFetch && $this->isName($node->name, $name));
            if ($usages !== []) {
                continue;
            }

            foreach ($class->stmts as $key => $stmt) {
                if (!$stmt instanceof ClassConst || !$stmt->isPrivate()) {
                    continue;
                }

                $stmt->consts = array_values(array_filter($stmt->consts, static fn ($const): bool => $const->name->toString() !== $name));
                if ($stmt->consts === []) {
                    unset($class->stmts[$key]);
                }
            }
        }

        $class->stmts = array_values($class->stmts);
    }

    /**
     * @param list<array{properties: array<string, Expr>, cookie: ?Expr, entries: ?list<array<string, Expr>>}> $groups
     */
    private function createListener(ClassMethod $method, array $groups): void
    {
        $stmts = [];
        foreach ($groups as $group) {
            array_push($stmts, ...($group['entries'] === null ? $this->createCookieGroup($group['properties'], $group['cookie']) : $this->createEntriesGroup($group['properties'], $group['entries'])));
        }

        // The method is changed in place instead of replaced, so the printer keeps the formatting of the class.
        $method->name = new Identifier('__invoke');
        $method->flags = Modifiers::PUBLIC;
        $method->attrGroups = [];
        $method->params = [new Param(new Variable('event'), null, $this->name(self::EVENT))];
        $method->returnType = new Identifier('void');
        $method->stmts = $stmts;
        $method->setAttribute('comments', []);
    }

    /**
     * A standalone cookie group is only added when no group with this name exists, the legacy array could not replace one either.
     *
     * @param array<string, Expr> $properties
     *
     * @return list<Stmt>
     */
    private function createCookieGroup(array $properties, ?Expr $cookie): array
    {
        $name = $properties['name'];
        unset($properties['name']);

        $create = [new Expression(new Assign(new Variable('cookieGroup'), new New_($this->name(self::COOKIE_GROUP), [new Arg($this->deepClone($name))])))];
        if (isset($properties['description'])) {
            $create[] = $this->assign('cookieGroup', 'description', $properties['description']);
            unset($properties['description']);
        }

        if ($cookie !== null) {
            $create[] = new Expression(new MethodCall(new Variable('cookieGroup'), 'setCookie', [new Arg($cookie)]));
        }

        foreach ($properties as $property => $value) {
            $create[] = $this->assign('cookieGroup', $property, $value);
        }

        $create[] = new Expression(new MethodCall($this->collection(), 'add', [new Arg(new Variable('cookieGroup'))]));

        return [new If_(new BooleanNot(new MethodCall($this->collection(), 'has', [new Arg($this->deepClone($name))])), ['stmts' => $create])];
    }

    /**
     * Entries are added to an existing group, for example a default group of Shopware, or to a new group.
     *
     * @param array<string, Expr> $properties
     * @param list<array<string, Expr>> $entries
     *
     * @return list<Stmt>
     */
    private function createEntriesGroup(array $properties, array $entries): array
    {
        $name = $properties['name'];
        unset($properties['name']);

        $group = new Variable('cookieGroup');
        $create = [new Expression(new Assign(new Variable('cookieGroup'), new New_($this->name(self::COOKIE_GROUP), [new Arg($this->deepClone($name))])))];
        foreach ($properties as $property => $value) {
            $create[] = $this->assign('cookieGroup', $property, $value);
        }

        $create[] = new Expression(new MethodCall($this->collection(), 'add', [new Arg(new Variable('cookieGroup'))]));

        $stmts = [
            new Expression(new Assign($group, new MethodCall($this->collection(), 'get', [new Arg($this->deepClone($name))]))),
            new If_(new Identical(new Variable('cookieGroup'), new ConstFetch(new Name('null'))), ['stmts' => $create]),
            new If_(
                new BooleanAnd(
                    new Identical(new MethodCall(new Variable('cookieGroup'), 'getEntries'), new ConstFetch(new Name('null'))),
                    new Identical(new MethodCall(new Variable('cookieGroup'), 'getCookie'), new ConstFetch(new Name('null'))),
                ),
                ['stmts' => [new Expression(new MethodCall(new Variable('cookieGroup'), 'setEntries', [new Arg(new New_($this->name(self::COOKIE_ENTRY_COLLECTION)))]))]],
            ),
        ];

        foreach ($entries as $entry) {
            $stmts[] = new Expression(new Assign(new Variable('cookieEntry'), new New_($this->name(self::COOKIE_ENTRY), [new Arg($entry['cookie'])])));
            unset($entry['cookie']);

            foreach ($entry as $property => $value) {
                $stmts[] = $this->assign('cookieEntry', $property, $value);
            }

            $stmts[] = new Expression(new NullsafeMethodCall(
                new MethodCall(new Variable('cookieGroup'), 'getEntries'),
                'add',
                [new Arg(new Variable('cookieEntry'))],
            ));
        }

        return $stmts;
    }

    private function assign(string $variable, string $property, Expr $value): Expression
    {
        return new Expression(new Assign(new PropertyFetch(new Variable($variable), $property), $value));
    }

    private function collection(): PropertyFetch
    {
        return new PropertyFetch(new Variable('event'), 'cookieGroupCollection');
    }

    private function deepClone(Expr $expr): Expr
    {
        // Cloned nodes must not keep the original positions, the format preserving printer would print the old tokens.
        $traverser = new NodeTraverser(new class extends NodeVisitorAbstract {
            public function enterNode(Node $node): Node
            {
                $clone = clone $node;
                $clone->setAttributes([]);

                return $clone;
            }
        });
        [$clone] = $traverser->traverse([$expr]);
        \assert($clone instanceof Expr);

        return $clone;
    }

    private function name(string $className): Name
    {
        return clone $this->names[$className];
    }

    /**
     * Decides for every class the listener needs whether it can be imported with its short name.
     *
     * @param array<Stmt> $stmts
     */
    private function prepareNames(array $stmts): void
    {
        $imported = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Use_ && $stmt->type === Use_::TYPE_NORMAL) {
                foreach ($stmt->uses as $use) {
                    $imported[strtolower($use->getAlias()->toString())] = $use->name->toString();
                }
            } elseif ($stmt instanceof GroupUse || ($stmt instanceof Stmt\ClassLike && $stmt->name !== null)) {
                // Keep it simple: names declared in the file or imported by group use are treated as taken.
                $names = $stmt instanceof GroupUse ? array_map(static fn (UseItem $use): string => $use->getAlias()->toString(), $stmt->uses) : [(string) $stmt->name];
                foreach ($names as $name) {
                    $imported[strtolower($name)] = '';
                }
            }
        }

        $this->names = [];
        $this->pendingImports = [];
        foreach ([self::EVENT, self::COOKIE_GROUP, self::COOKIE_ENTRY, self::COOKIE_ENTRY_COLLECTION] as $className) {
            $shortName = substr($className, (int) strrpos($className, '\\') + 1);
            $existing = $imported[strtolower($shortName)] ?? null;
            if ($existing === null || $existing === $className) {
                $this->names[$className] = new Name($shortName);
                if ($existing === null) {
                    $this->pendingImports[$className] = $shortName;
                }

                continue;
            }

            $this->names[$className] = new Name\FullyQualified($className);
        }
    }

    /**
     * Removes the import of the legacy interface when it is no longer used and imports the classes of the listener.
     *
     * @param array<Stmt> $stmts
     */
    private function updateImports(array &$stmts): void
    {
        $nodeFinder = new NodeFinder();
        $code = array_filter($stmts, static fn (Stmt $stmt): bool => !$stmt instanceof Use_ && !$stmt instanceof GroupUse);
        $stillUsed = $nodeFinder->findFirst($code, fn (Node $node): bool => $node instanceof Name && $this->isName($node, self::COOKIE_PROVIDER_INTERFACE)) !== null;

        $usedNames = [];
        $nodeFinder->find($code, static function (Node $node) use (&$usedNames): bool {
            if ($node instanceof Name && !$node instanceof Name\FullyQualified) {
                $usedNames[$node->toString()] = true;
            }

            return false;
        });

        $imports = [];
        ksort($this->pendingImports);
        foreach ($this->pendingImports as $className => $shortName) {
            if (isset($usedNames[$shortName])) {
                $imports[] = new Use_([new UseItem(new Name($className))]);
            }
        }

        $result = [];
        $insertAt = null;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Use_ && $stmt->type === Use_::TYPE_NORMAL) {
                if (!$stillUsed) {
                    $stmt->uses = array_values(array_filter($stmt->uses, fn (UseItem $use): bool => !$this->isName($use->name, self::COOKIE_PROVIDER_INTERFACE)));
                }

                if ($stmt->uses === [] && $imports !== []) {
                    // Reuse the import statement, so its surrounding blank lines are kept.
                    $last = array_pop($imports);
                    $stmt->uses = $last->uses;
                    array_push($result, ...$imports);
                    $imports = [];
                }

                if ($stmt->uses !== []) {
                    $result[] = $stmt;
                }

                $insertAt = count($result);

                continue;
            }

            $result[] = $stmt;
            if ($stmt instanceof Declare_ && $insertAt === null) {
                $insertAt = count($result);
            }
        }

        array_splice($result, $insertAt ?? 0, 0, $imports);
        $stmts = $result;
        $this->pendingImports = [];
    }
}
