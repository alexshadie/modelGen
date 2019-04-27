<?php

$localDir = __DIR__;
if (strpos($localDir, '/vendor/alexshadie/model-gen/bin') === strlen($localDir) - 32) {
    // Vendor directory, works as library
    $configPath = substr($localDir, 0, strlen($localDir) - 32) . '/config';
    $vendorPath = substr($localDir, 0, strlen($localDir) - 32) . '/vendor';
} else {
    $configPath = substr($localDir, 0, strlen($localDir) - 4) . '/config';
    $vendorPath = substr($localDir, 0, strlen($localDir) - 4) . '/vendor';
}

require_once $vendorPath . '/autoload.php';

if (!is_dir($configPath)) {
    die("Config not found in {$configPath}\n");
}
echo "Using config from '{$configPath}'\n";
$config = @include($configPath . '/mgen-config.php');
$outputPath = realpath($config['path']);

$model = $argv[1] ?? '';
$out = $argv[2] ?? '';

if (!$model || !preg_match('!^[A-Za-z/]+$!i', $model)) {
    die("Specify model");
}

echo "Generating {$model}\n";

$modelStructure = include $config['modelpath'] . '/' . $model . '.php';
$modelHash = md5_file($config['modelpath'] . '/' . $model . '.php');

$position = strrpos($model, '/');
if (!$position) {
    $position = 0;
} else {
    $position += 1;
}
$model = substr($model, $position);
if (isset($modelStructure['name'])) {
    $model = $modelStructure['name'];
}

$namespace = $modelStructure['ns'];
if ($config['coreNamespace'] ?? null) {
    $namespace = $config['coreNamespace'] . "\\" . $modelStructure['ns'];
}

switch ($modelStructure['type']) {
    case 'model':
        $mFile = new \mgen\ModelFile($modelHash);
        $mFile->setName($model);
        $mFile->setNamespace($namespace ?? null);
        $mFile->setFields($modelStructure['fields']);
        $mFile->setExports($modelStructure['exports'] ?? []);
        $mFile->setUseCoreUtils($modelStructure['useCoreUtils'] ?? true);

        $bFile = new \mgen\BuilderFile($modelHash);
        $bFile->setName($model);
        $bFile->setNamespace($namespace ?? null);
        $bFile->setFields($modelStructure['fields']);
        $bFile->setDefaults($modelStructure['defaults'] ?? []);
        $bFile->setExports($modelStructure['exports'] ?? []);


        $sqlFile = new \mgen\SQLFile($modelHash);
        $sqlFile->setName($model);
        $sqlFile->setNamespace($namespace ?? null);
        $sqlFile->setFields($modelStructure['fields']);
        $sqlFile->setExports($modelStructure['exports'] ?? []);

        $testFile = new \mgen\TestFile($modelHash);
        $testFile->setName($model);
        $testFile->setNamespace($namespace ?? null);
        $testFile->setFields($modelStructure['fields']);
        $testFile->setExports($modelStructure['exports'] ?? []);


        if ($out) {
            $relPath = $modelStructure['path'] ?? str_replace("\\", "/", $modelStructure['ns']);
            $mFile->write($outputPath . "/src/" . $relPath . '/', $model . ".php");
            $bFile->write($outputPath . "/src/" . $relPath . '/', $model . "Builder.php");
            $testFile->write($outputPath . "/tests/unit/" . $relPath . '/', $model . "Test.php");
        } else {
            echo "\n\n========================= Model ===============================\n";
            echo $mFile->generate();
            echo "\n\n========================= Builder =============================\n";
            echo $bFile->generate();
            echo "\n\n========================= SQL =================================\n";
            echo $sqlFile->generate();
            echo "\n\n========================= Test ================================\n";
            echo $testFile->generate();
        }
        break;

    case 'svc':
        $struct = [];
        foreach ($modelStructure['models'] as $modelName) {
            $struct[] = [
                'name' => $modelName,
                'structure' => include $config['modelpath'] . '/' . $modelName . '.php',
            ];
        }

        $repositoryFile = new \mgen\RepositoryFile($modelHash);
        $repositoryFile->setName($model);

        $repositoryTestFile = '';

        $serviceFile = new \mgen\ServiceFile($modelHash);;
        $serviceFile->setName($model);


        $serviceTestFile = '';

        $relPath = $modelStructure['path'] ?? str_replace("\\", "/", $modelStructure['ns']);
        $repositoryFile->write($outputPath . "/src/" . $relPath . '/', $model . "Repository.php");
        $serviceFile->write($outputPath . "/src/" . $relPath . '/', $model . "Service.php");

        break;
}
