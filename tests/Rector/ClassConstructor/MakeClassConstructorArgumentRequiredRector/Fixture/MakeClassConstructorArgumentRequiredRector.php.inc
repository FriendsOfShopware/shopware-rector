<?php

class Foo {
    public function __construct(array $arg1, array $arg2 = null)
    {
    }
}

?>

-----
<?php

class Foo {
    public function __construct(array $arg1, array $arg2)
    {
    }
}

?>

