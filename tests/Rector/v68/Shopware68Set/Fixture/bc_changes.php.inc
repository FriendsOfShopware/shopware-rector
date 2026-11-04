<?php

namespace Shopware\Core\Framework\DataAbstractionLayer\Search\Term;

interface TokenizerInterface
{
    public function tokenize(string $string): array;
}

class CustomTokenizer implements TokenizerInterface
{
    public function tokenize(string $string): array { return []; }
}
-----
<?php

namespace Shopware\Core\Framework\DataAbstractionLayer\Search\Term;

interface TokenizerInterface
{
    public function tokenize(string $string): array;
}

class CustomTokenizer implements TokenizerInterface
{
    public function tokenize(string $string, ?int $tokenMinimumLength = null): array { return []; }
}
