<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Rule\Rules\Class_\MustBeFinalRule;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY(), Preset::YAGNI())
    ->rule('source.must_be_final', new MustBeFinalRule('Source'))
    ->layer('Class_', 'src/Rule/Class_/')
    ->layer('ClassConstructor', 'src/Rule/ClassConstructor/')
    ->layer('ClassMethod', 'src/Rule/ClassMethod/')
    ->layer('BCChange', 'src/Rule/BCChange/')
    ->layer('Generator', 'src/Generator/')
    ->layer('Transform', 'src/Rule/Transform/')
    ->layerPattern('Version', '#^Frosh\\\Rector\\\Rule\\\v\d+\\\#')
    ->layer('Set', 'src/Set/')
    ->ruleset([
        'Class_' => [],
        'ClassConstructor' => ['+ClassMethod'],
        'ClassMethod' => [],
        'BCChange' => [],
        'Generator' => ['+BCChange'],
        'Transform' => [],
        'Version' => [],
        'Set' => [],
    ])
;
