<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'League\Flysystem\FilesystemInterface' => 'League\Flysystem\FilesystemOperator',
            'League\Flysystem\AdapterInterface' => 'League\Flysystem\FilesystemAdapter',
            'League\Flysystem\Memory\MemoryAdapter' => 'League\Flysystem\InMemory\InMemoryFilesystemAdapter',
        ],
    );

    $methodRenaming = [];
    $classConstRenaming = [];

    $filesystemClasses = [
        'League\Flysystem\FilesystemOperator',
        'League\Flysystem\Filesystem',
        'League\Flysystem\FilesystemInterface',
        'League\Flysystem\FilesystemAdapter',
    ];
    foreach ($filesystemClasses as $class) {
        $methodRenaming += [
            new MethodCallRename($class, 'rename', 'move'),
            // No arbitrary abbreviations
            new MethodCallRename($class, 'createDir', 'createDirectory'),
            new MethodCallRename($class, 'deleteDir', 'deleteDirectory'),
            // Writes are now deterministic
            new MethodCallRename($class, 'update', 'write'),
            new MethodCallRename($class, 'updateStream', 'writeStream'),
            new MethodCallRename($class, 'put', 'write'),
            new MethodCallRename($class, 'putStream', 'writeStream'),
            // Metadata getters are renamed
            new MethodCallRename($class, 'getTimestamp', 'lastModified'),
            new MethodCallRename($class, 'has', 'fileExists'),
            new MethodCallRename($class, 'getMimetype', 'mimeType'),
            new MethodCallRename($class, 'getSize', 'fileSize'),
            new MethodCallRename($class, 'getVisibility', 'visibility'),
        ];

        $classConstRenaming[] = new RenameClassAndConstFetch($class, 'VISIBILITY_PUBLIC', 'League\Flysystem\Visibility', 'PUBLIC');
        $classConstRenaming[] = new RenameClassAndConstFetch($class, 'VISIBILITY_PRIVATE', 'League\Flysystem\Visibility', 'PRIVATE');
    }

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, $methodRenaming);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, $classConstRenaming);
};
