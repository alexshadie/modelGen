<?php

include __DIR__ . "/lib/func.php";
include __DIR__ . "/lib/Type.php";
include __DIR__ . "/lib/CommonFile.php";
include __DIR__ . "/lib/ModelFile.php";
include __DIR__ . "/lib/SQLFile.php";
include __DIR__ . "/lib/BuilderFile.php";
include __DIR__ . "/lib/TestFile.php";

$model = $argv[1] ?? '';
$out = $argv[2] ?? '';

$config = @include(__DIR__ . "/config/config.php");

$outputPath = realpath($config['path']);

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


$mFile = new ModelFile($modelHash);
$mFile->setName($model);
$mFile->setNamespace($modelStructure['ns'] ?? null);
$mFile->setFields($modelStructure['fields']);
$mFile->setExports($modelStructure['exports'] ?? []);
$mFile->setUseCoreUtils($modelStructure['useCoreUtils'] ?? true);

$bFile = new BuilderFile($modelHash);
$bFile->setName($model);
$bFile->setNamespace($modelStructure['ns'] ?? null);
$bFile->setFields($modelStructure['fields']);
$bFile->setExports($modelStructure['exports'] ?? []);


$sqlFile = new SQLFile($modelHash);
$sqlFile->setName($model);
$sqlFile->setNamespace($modelStructure['ns'] ?? null);
$sqlFile->setFields($modelStructure['fields']);
$sqlFile->setExports($modelStructure['exports'] ?? []);

$testFile = new TestFile($modelHash);
$testFile->setName($model);
$testFile->setNamespace($modelStructure['ns'] ?? null);
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
