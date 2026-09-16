<?php

class CoreClass
{
    public function load(string $id): object {}

    public function enabled(bool $enabled = false): void {}

    public function rename(string $required, bool $optional = false, ?string $oldName = null): void {}

    public function future(?string $oldName = null): void {}
}

final class Extension extends CoreClass
{
    public function load(string $id): object {}
}

final class CompatibleExtension extends CoreClass
{
    public function load(mixed $id): static {}
}

$core = new CoreClass();
$core->enabled();
$core->rename('required', oldName: 'value');
$core->future(oldName: 'unchanged');

?>
-----
<?php

class CoreClass
{
    public function load(int|string $id, ?DateTimeInterface $fresh = null): static {}

    public function enabled(bool $enabled = false): void {}

    public function rename(string $required, bool $optional = false, ?string $oldName = null): void {}

    public function future(?string $oldName = null): void {}
}

final class Extension extends CoreClass
{
    public function load(int|string $id, ?DateTimeInterface $fresh = null): static {}
}

final class CompatibleExtension extends CoreClass
{
    public function load(mixed $id, ?DateTimeInterface $fresh = null): static {}
}

$core = new CoreClass();
$core->enabled(enabled: false);
$core->rename('required', false, 'value');
$core->future(oldName: 'unchanged');

?>
