<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Config;

use PHPUnit\Framework\TestCase;

final class ShopwareConfigSymfonySetCompatibilityTest extends TestCase
{
    /**
     * @dataProvider provideShopwareConfigFiles
     */
    public function testConfigDoesNotUseRemovedSymfonyVersionConstants(string $configFile): void
    {
        $content = (string) file_get_contents($configFile);

        self::assertStringNotContainsString('SymfonySetList::SYMFONY_', $content);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideShopwareConfigFiles(): iterable
    {
        $files = glob(__DIR__ . '/../../config/shopware-*.php') ?: [];

        if ($files === []) {
            throw new \RuntimeException('No Shopware config files found for compatibility test.');
        }

        foreach ($files as $file) {
            yield [$file];
        }
    }
}
