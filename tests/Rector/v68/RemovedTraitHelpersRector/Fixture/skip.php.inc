<?php

class OtherTest
{
    public function run($expected, $actual): void { $this->assertValues($expected, $actual); }
}
class AliasedTest
{
    use \Shopware\Core\Framework\Test\Api\Serializer\AssertValuesTrait { assertValues as custom; }
}
class OwnTest
{
    use \Shopware\Core\Framework\Test\Api\Serializer\AssertValuesTrait;
    protected function assertValues($a, $b): void {}
    public function run(): void { $this->assertValues([], []); }
}
