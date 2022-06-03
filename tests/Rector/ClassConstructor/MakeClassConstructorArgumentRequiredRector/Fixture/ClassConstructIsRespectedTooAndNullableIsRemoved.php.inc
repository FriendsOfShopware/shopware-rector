<?php

class Baz {
    public function __construct(array $arg1, array $arg2 = null)
    {
    }
}

new Baz([])

?>

-----
<?php

class Baz {
    public function __construct(array $arg1, array $arg2)
    {
    }
}

new Baz([], null)

?>

