<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source;

abstract class CoreClass
{
    public function load(string $id): object {}

    public function enabled(bool $enabled = false): void {}
}
