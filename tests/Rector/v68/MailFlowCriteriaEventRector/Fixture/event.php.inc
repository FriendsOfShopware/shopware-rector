<?php

function handle(\Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent $event): void
{
    $event->getCriteria()->addAssociation('customer');
    $name = $event?->getEntityName();
    $context = $event->getContext();
    $events = ['flow.storer.order.criteria.event' => 'handle'];
}
-----
<?php

function handle(\Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent $event): void
{
    $event->criteria->addAssociation('customer');
    $name = $event?->entityName;
    $context = $event->getContext();
    $events = ['mail-flow.data.order.criteria.event' => 'handle'];
}
