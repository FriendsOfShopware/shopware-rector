<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}

function firstEntity(EntitySearchResult $result): ?object
{
    return $result->first();
}

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->isRecalculation();
}

?>
-----
<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class ProductExtension extends EntityExtension
{
    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}

function firstEntity(EntitySearchResult $result): ?object
{
    return $result->getEntities()->first();
}

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->isRecalculation();
}

?>
