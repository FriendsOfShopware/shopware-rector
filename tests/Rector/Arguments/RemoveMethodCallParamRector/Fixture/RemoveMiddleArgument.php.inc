<?php

final class Caller
{
    public static function process(...$args): void {}
}

function run(Caller $caller, object $other): void
{
    $caller->process(1, 2, 3);
    Caller::process(1, 2, 3);
    $other->process(1, 2, 3);
    $caller->process(...);
    Caller::process(...);
}

-----
<?php

final class Caller
{
    public static function process(...$args): void {}
}

function run(Caller $caller, object $other): void
{
    $caller->process(1, 3);
    Caller::process(1, 3);
    $other->process(1, 2, 3);
    $caller->process(...);
    Caller::process(...);
}
