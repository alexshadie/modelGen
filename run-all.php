<?php

$config = @include(__DIR__ . "/config/config.php");

$modelPath = realpath($config['modelpath']);

$allModels = [];

function collectAllModels($path)
{
    $it = new DirectoryIterator($path);
    foreach ($it as $item) {
        if ($item->isDot()) {
            continue;
        }
        if ($item->isDir()) {
            yield from collectAllModels($item->getPathname());
            continue;
        }
        yield $item->getPathname();
    }
}

foreach (collectAllModels($modelPath) as $item) {
    $item = str_replace($modelPath . "/", "", $item);
    $item = str_replace(".php", "", $item);

    passthru("php run.php {$item} 1");
}
