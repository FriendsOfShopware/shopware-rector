<?php

namespace Frosh\Rector\Tests\Rector\v65\AbstractMessageHandlerToMessageSubscriberRector\Fixture;

use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;

class MyMessage {}

class MyMessageHandler extends AbstractMessageHandler
{
    public static function getHandledMessages(): iterable
    {
        return [MyMessage::class];
    }

    public function handle(MyMessage $message): void
    {
        // do something
    }
}
?>
-----
<?php

namespace Frosh\Rector\Tests\Rector\v65\AbstractMessageHandlerToMessageSubscriberRector\Fixture;

use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;

class MyMessage {}

#[\Symfony\Component\Messenger\Attribute\AsMessageHandler]
class MyMessageHandler implements \Symfony\Component\Messenger\Handler\MessageSubscriberInterface
{
    public function __invoke(MyMessage $message): void
    {
        // do something
    }
}
?>
