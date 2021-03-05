<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator
        ->services()
        ->set('fix_const', RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassConstFetch('Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\\SearchRanking', 'LOW_SEARCH_RAKING', 'LOW_SEARCH_RANKING')
            ])
        ]]);
};