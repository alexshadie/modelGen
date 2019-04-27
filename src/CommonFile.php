<?php

namespace mgen;

define('S', "    ");

abstract class CommonFile
{
    protected $namespace;
    protected $name;
    protected $fields;
    protected $defaults;
    protected $exports;
    protected $modelHash;
    protected $useCoreUtils;

    public function __construct($modelHash)
    {
        $this->modelHash = $modelHash;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    public function setExports($exports)
    {
        $this->exports = $exports;
    }

    public function setUseCoreUtils($useCoreUtils)
    {
        $this->useCoreUtils = !!$useCoreUtils;
    }

    public function write($path, $file)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if (is_file($path . '/' . $file)) {
            $f = fopen($path . "/" . $file, "r");
            fgets($f);
            fgets($f);
            fgets($f);
            $v = fgets($f);
            fclose($f);
            if (preg_match('! * VERSION: ([a-z0-9]+)!', $v, $m)) {
                $v = $m[1];
            } else {
                $v = null;
            }
            if ($v === $this->modelHash) {
                echo "File \"{$file}\" is up to date\n";
                return;
            }
        }
        file_put_contents($path . '/' . $file, $this->generate());

    }

    abstract public function generate();
}