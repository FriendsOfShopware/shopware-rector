<?php

namespace Shopware\Core\Checkout\Document\Zugferd {
    class ZugferdDocument {
        protected function getPrice($tax): float { return 0.0; }
        protected function getPriceWithFallback($tax, $fallbackPrice = null): float { return 0.0; }
    }
}
namespace App {
    class CustomDocument extends \Shopware\Core\Checkout\Document\Zugferd\ZugferdDocument {
        protected function getPrice($tax): float { return 1.0; }
        public function calculate($tax): float { return $this->getPrice($tax); }
    }
}
