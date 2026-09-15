<?php

class TargetCoreClass
{
    public function rename(?string $newName = null): void {}

    public function remove(string $id): void {}

    public function removeUsed(string $id): void {}

    public function required(string $id, object $context): void {}

    public function enabled(bool $enabled = true): void {}

    public function bridgeRename(?string $oldName = null): void {}
}

final class TargetExtension extends TargetCoreClass
{
    public function rename(?string $oldName = null): void
    {
        echo $oldName;
    }

    public function remove(string $id, ?string $obsolete = null): void {}

    public function removeUsed(string $id, ?string $obsolete = null): void
    {
        echo $obsolete;
    }

    public function required(string $id): void
    {
        /** @var object $context */
        $context = func_get_arg(1);
        $callback = static function (): mixed {
            return func_get_arg(1);
        };
        consume($context);
    }
}

final class NestedTargetExtension extends TargetCoreClass
{
    public function rename(?string $oldName = null): void
    {
        $callback = static function (?string $oldName): void {
            echo $oldName;
        };
        echo $oldName;
    }
}

$core = new TargetCoreClass();
$core->rename(oldName: 'value');
$core->remove('id', 'obsolete');
$core->remove('id', createAuditEntry());
$core->enabled();
$core->bridgeRename(oldName: 'bridge');

?>
-----
<?php

class TargetCoreClass
{
    public function rename(?string $newName = null): void {}

    public function remove(string $id): void {}

    public function removeUsed(string $id): void {}

    public function required(string $id, object $context): void {}

    public function enabled(bool $enabled = true): void {}

    public function bridgeRename(?string $oldName = null): void {}
}

final class TargetExtension extends TargetCoreClass
{
    public function rename(?string $newName = null): void
    {
        echo $newName;
    }

    public function remove(string $id): void {}

    public function removeUsed(string $id, ?string $obsolete = null): void
    {
        echo $obsolete;
    }

    public function required(string $id, object $context): void
    {
        $callback = static function (): mixed {
            return func_get_arg(1);
        };
        consume($context);
    }
}

final class NestedTargetExtension extends TargetCoreClass
{
    public function rename(?string $oldName = null): void
    {
        $callback = static function (?string $oldName): void {
            echo $oldName;
        };
        echo $oldName;
    }
}

$core = new TargetCoreClass();
$core->rename(newName: 'value');
$core->remove('id');
$core->remove('id', createAuditEntry());
$core->enabled(enabled: false);
$core->bridgeRename('bridge');

?>
