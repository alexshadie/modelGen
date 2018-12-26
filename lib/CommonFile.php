<?php

define('S', "    ");

abstract class CommonFile
{
    protected $namespace;
    protected $name;
    protected $fields;
    protected $exports;

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

    public function setExports($exports)
    {
        $this->exports = $exports;
    }

    abstract public function generate();
}