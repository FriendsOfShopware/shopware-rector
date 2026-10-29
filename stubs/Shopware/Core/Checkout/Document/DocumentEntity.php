<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Document;

abstract class DocumentEntity
{
    public function getDecorated(): self {}
}
