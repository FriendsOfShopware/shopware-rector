<?php

class Foo {
    public function __construct(array $arg1, array $arg2, array $arg3, array $arg4)
    {
    }
}

?>

-----
<?php

class Foo {
    public function __construct(array $arg1, array $arg4)
    {
    }
}

?>

