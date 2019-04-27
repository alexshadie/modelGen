<?php

$localDir = __DIR__;
if (strpos($localDir, '/vendor/bin') === strlen($localDir) - 11) {
    // Vendor directory, works as library
    $configPath = substr($localDir, 0, strlen($localDir) - 11) . '/config';
} else {
    $configPath = substr($localDir, 0, strlen($localDir) - 4) . '/config';
}

if (!is_dir($configPath)) {
    die("Config not found in {$configPath}\n");
}

echo "Using config from '{$configPath}'\n";

$config = @include($configPath . '/mgen-config.php');

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

    passthru("php " . __DIR__ . "/mgen-model.php {$item} 1");
}
