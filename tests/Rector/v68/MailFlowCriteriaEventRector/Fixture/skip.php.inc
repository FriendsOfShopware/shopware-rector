<?php

function other(\Unrelated $event): void
{
    $event->getCriteria();
    $event->getEntityName();
    $name = 'prefix.flow.storer.order.criteria.event';
}
