<?php

use Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source\CallDefaults;

function defaults(?CallDefaults $defaults, array $arguments): void
{
    new CallDefaults();
    new CallDefaults(true);
    new CallDefaults(enabled: true);
    new CallDefaults(...$arguments);
    CallDefaults::create();
    CallDefaults::create(...);
    $defaults->configure();
    $defaults?->configure();
    $defaults->configure(limit: 20);
    $defaults->configure(enabled: true);
    $defaults->configure(true, 20);
    $defaults->configure(...$arguments);
    $defaults->configure(...);
}
-----
<?php

use Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source\CallDefaults;

function defaults(?CallDefaults $defaults, array $arguments): void
{
    new CallDefaults(enabled: false);
    new CallDefaults(true);
    new CallDefaults(enabled: true);
    new CallDefaults(...$arguments);
    CallDefaults::create(enabled: false);
    CallDefaults::create(...);
    $defaults->configure(enabled: false, limit: 10);
    $defaults?->configure(enabled: false, limit: 10);
    $defaults->configure(limit: 20, enabled: false);
    $defaults->configure(enabled: true, limit: 10);
    $defaults->configure(true, 20);
    $defaults->configure(...$arguments);
    $defaults->configure(...);
}
