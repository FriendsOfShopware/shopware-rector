<?php

use Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source\CoreClass;

final class Extension extends CoreClass
{
    public function load(string $id): object {}
}

final class CompatibleExtension extends CoreClass
{
    public function load(mixed $id): static {}
}

final class VariadicExtension extends CoreClass
{
    public function load(string $id, ...$arguments): object {}
}

final class AlreadyCompatibleExtension extends CoreClass
{
    public function load(mixed $id, ?DateTimeInterface $fresh = null): static {}
}

$core = new Extension();
$core->enabled();

?>
-----
<?php

use Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source\CoreClass;

final class Extension extends CoreClass
{
    public function load(int|string $id, ?DateTimeInterface $fresh = null): static {}
}

final class CompatibleExtension extends CoreClass
{
    public function load(mixed $id, ?DateTimeInterface $fresh = null): static {}
}

final class VariadicExtension extends CoreClass
{
    public function load(int|string $id, ...$arguments): static {}
}

final class AlreadyCompatibleExtension extends CoreClass
{
    public function load(mixed $id, ?DateTimeInterface $fresh = null): static {}
}

$core = new Extension();
$core->enabled(enabled: false);

?>
