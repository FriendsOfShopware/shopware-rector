<?php declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

class RoutingConfigurator
{
    public function import(string|array $resource, ?string $type = null, bool $ignoreErrors = false, string|array|null $exclude = null): object
    {
        return $this;
    }
}
