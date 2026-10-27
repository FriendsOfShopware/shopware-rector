<?php

class CustomBase extends \Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition
{
    public function __construct() { initialize(); }
}
class Child extends CustomBase
{
    public function __construct() { parent::__construct(); }
}
