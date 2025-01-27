<?php
namespace Frosh\Rector\Tests\Rector\v67\AddEntityNameToEntityExtension\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new StringField('name', 'name'));
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
?>
-----
<?php
namespace Frosh\Rector\Tests\Rector\v67\AddEntityNameToEntityExtension\Fixture;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new StringField('name', 'name'));
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
?>
