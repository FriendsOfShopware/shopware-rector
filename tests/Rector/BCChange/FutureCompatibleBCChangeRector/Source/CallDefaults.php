<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\BCChange\FutureCompatibleBCChangeRector\Source;

final class CallDefaults
{
    public function __construct(bool $enabled = false) {}

    public static function create(bool $enabled = false): self {}

    public function configure(bool $enabled = false, int $limit = 10): void {}
}
