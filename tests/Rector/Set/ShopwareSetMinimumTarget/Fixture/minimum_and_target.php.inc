<?php

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}

class TaskHandler extends ScheduledTaskHandler
{
    public function __construct(EntityRepository $repository)
    {
        parent::__construct($repository);
    }
}

?>
-----
<?php

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}

class TaskHandler extends ScheduledTaskHandler
{
    public function __construct(EntityRepository $repository, LoggerInterface $exceptionLogger)
    {
        parent::__construct($repository, $exceptionLogger);
    }
}

?>
