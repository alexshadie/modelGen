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

$modelStructure = include $config['modelpath'] . '/' . $model . '.php';
$position = strrpos($model, '/');
if (!$position) {
    $position = 0;
} else {
    $position += 1;
}
$model = substr($model, $position);


$mFile = new ModelFile();
$mFile->setName($model);
$mFile->setNamespace($modelStructure['ns'] ?? null);
$mFile->setFields($modelStructure['fields']);
$mFile->setExports($modelStructure['exports']);

$bFile = new BuilderFile();
$bFile->setName($model);
$bFile->setNamespace($modelStructure['ns'] ?? null);
$bFile->setFields($modelStructure['fields']);
$bFile->setExports($modelStructure['exports']);


$sqlFile = new SQLFile();
$sqlFile->setName($model);
$sqlFile->setNamespace($modelStructure['ns'] ?? null);
$sqlFile->setFields($modelStructure['fields']);
$sqlFile->setExports($modelStructure['exports']);

$testFile = new TestFile();
$testFile->setName($model);
$testFile->setNamespace($modelStructure['ns'] ?? null);
$testFile->setFields($modelStructure['fields']);
$testFile->setExports($modelStructure['exports']);


if ($out) {
//    var_export($outputPath . "/src/" . str_replace("\\", "/", $modelStructure['ns']) . "/" . $model . ".php");
//    die();
    if (!is_dir($outputPath . "/src/" . str_replace("\\", "/", $modelStructure['ns']))) {
        mkdir($outputPath . "/src/" . str_replace("\\", "/", $modelStructure['ns']), 0755, 1);
    }
    file_put_contents($outputPath . "/src/" . str_replace("\\", "/", $modelStructure['ns']) . "/" . $model . ".php", $mFile->generate());
    file_put_contents($outputPath . "/src/" . str_replace("\\", "/", $modelStructure['ns']) . "/" . $model . "Builder.php", $bFile->generate());
    if (!is_dir($outputPath . "/tests/unit/" . str_replace("\\", "/", $modelStructure['ns']))) {
        mkdir($outputPath . "/tests/unit/" . str_replace("\\", "/", $modelStructure['ns']), 0755, 1);
    }
    file_put_contents($outputPath . "/tests/unit/" . str_replace("\\", "/", $modelStructure['ns']) . "/" . $model . "Test.php", $testFile->generate());
} else {
//    echo "\n\n========================= Model ===============================\n";
//    echo $mFile->generate();
//    echo "\n\n========================= Builder =============================\n";
//    echo $bFile->generate();
//    echo "\n\n========================= SQL =================================\n";
//    echo $sqlFile->generate();
    echo "\n\n========================= Test ================================\n";
    echo $testFile->generate();
}
