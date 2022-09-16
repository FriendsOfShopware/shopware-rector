<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateRetrievePaymentHandlerFromPaymentHandlerRegistryRector\Fixture;

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;

$paymentHandlerRegistry = new PaymentHandlerRegistry();

$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getHandlerForPaymentMethod($paymentMethod);
?>

-----
<?php
namespace Frosh\Rector\Tests\Rector\v65\MigrateRetrievePaymentHandlerFromPaymentHandlerRegistryRector\Fixture;

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;

$paymentHandlerRegistry = new PaymentHandlerRegistry();

$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getPaymentMethodHandler($paymentMethod->getId());
?>
