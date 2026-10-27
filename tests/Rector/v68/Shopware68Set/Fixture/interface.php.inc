<?php

namespace Shopware\Core\System\NumberRange\ValueGenerator {
    interface NumberRangeValueGeneratorInterface {}
    abstract class AbstractNumberRangeValueGenerator {}
}
namespace App {
    class Generator implements \Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface {}
    class Base {}
    class WithParent extends Base implements \Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface {}
}
-----
<?php

namespace Shopware\Core\System\NumberRange\ValueGenerator {
    interface NumberRangeValueGeneratorInterface {}
    abstract class AbstractNumberRangeValueGenerator {}
}
namespace App {
    class Generator extends \Shopware\Core\System\NumberRange\ValueGenerator\AbstractNumberRangeValueGenerator
    {
    }
    class Base {}
    class WithParent extends Base implements \Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface {}
}
