<?php

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

$place = $_SERVER['argv'][1];
$name = $_SERVER['argv'][2];

$rectorTpl = <<<'RECTOR'
<?php

namespace #NAMESPACE#;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class #NAME# extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class Foo implements Test {

}
CODE_SAMPLE
                ,
                <<<'PHP'
class Foo extends AbstractTest {

}
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    /**
     * @param Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return null;
    }
}
RECTOR;

$vals = [
    '#NAMESPACE#' => 'Frosh\Rector\Rule\\' . $place,
    '#NAME#' => $name,
];
$rector = str_replace(array_keys($vals), $vals, $rectorTpl);

file_put_contents(sprintf('%s/src/Rule/%s/%s.php', __DIR__, $place, $name), $rector,LOCK_EX);