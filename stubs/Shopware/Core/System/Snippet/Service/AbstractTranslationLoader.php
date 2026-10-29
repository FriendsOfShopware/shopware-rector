<?php declare(strict_types=1);

namespace Shopware\Core\System\Snippet\Service;

abstract class AbstractTranslationLoader
{
    abstract public function getDecorated(): self;

    public function pluginTranslationExists(\Shopware\Core\Framework\Plugin $plugin): bool {}
}
