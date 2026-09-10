<?php

declare(strict_types=1);

use Boundwize\StructArmed\Rule\Rules\Class_\MustBeFinalRule;
use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY(), Preset::YAGNI())
    ->rule('source.must_be_final', new MustBeFinalRule('Source'))
;
