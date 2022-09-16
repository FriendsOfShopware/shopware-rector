<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateRetrievePaymentHandlerFromPaymentHandlerRegistryRector\Fixture;

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;

$paymentHandlerRegistry = new PaymentHandlerRegistry();

$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getAsyncHandlerForPaymentMethod($paymentMethod);
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateRetrievePaymentHandlerFromPaymentHandlerRegistryRector\Fixture;

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;

$paymentHandlerRegistry = new PaymentHandlerRegistry();

$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getAsyncPaymentHandler($paymentMethod->getId());
?>
