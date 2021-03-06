<?php

namespace mgen;

class BuilderFile extends CommonFile
{
    public function generate()
    {
        $content = ["<?php", "/*", " * This file was generated by modelGen", " * VERSION: {$this->modelHash}", " * https://github.com/alexshadie/modelGen", " */", ""];
        if ($this->namespace) {
            $content[] = "namespace {$this->namespace};";
            $content[] = "";
        }

        $content[] = "class {$this->name}Builder \n{";
        $content[] = $this->buildFields();

        $content[] = $this->getBuilderMethods();

        $content[] = $this->getSetters();
        $content[] = "}";
        $content[] = "";
        return join("\n", $content);
    }

    protected function buildFields()
    {
        $phpDocs = [];
        $fields = [];
        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            $phpDoc = S . "/** \n     * @var {$t->getPhpDocType()}\n";
            $phpDoc .= S . " */";
            $phpDocs[] = $phpDoc;
            $fieldStr = S . "private \${$field}";
            if (isset($this->defaults[$field])) {
                $fieldStr .= " = " . $t->getQuotedValue($this->defaults[$field]);;
            }
            $fieldStr .= ";";
            $fields[] = $fieldStr;
        }

        $result = [];
        foreach ($phpDocs as $k => $v) {
            $result[] = $phpDocs[$k];
            $result[] = $fields[$k];
            $result[] = "";
        }

        return join("\n", $result);
    }

    public function getBuilderMethods()
    {
        $fromContent = [];
        $fromArrayContent = [];
        $createContent = [];
        $idx = 0;

        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            $fromContent[] = "        \$builder->set" . ucfirst($field) . "(\$src->get" . ucfirst($field) . "());";
            $default = isset($this->defaults[$field]) ? $t->getQuotedValue($this->defaults[$field]) : $t->getDefaultValue();
            if (Helper::camelCaseToUnderscores($field) === $field) {
                $fromArrayContent[] = "        \$builder->set" . ucfirst($field) . "(\$src[\"$field\"] ?? \$src[$idx] ?? $default);";
            } else {
                $fromArrayContent[] = "        \$builder->set" . ucfirst($field) . "(\$src[\"" . Helper::camelCaseToUnderscores($field) . "\"] ?? \$src[\"$field\"] ?? \$src[$idx] ?? $default);";
            }
            $createContent[] = "            \$this->{$field}";
            $idx++;
        }

        $builderMethods = [];
        $builderMethods[] = "    public static function from({$this->name} \$src): {$this->name}Builder\n    {\n        \$builder = new {$this->name}Builder();";
        $builderMethods = array_merge($builderMethods, $fromContent);
        $builderMethods[] = "        return \$builder;";
        $builderMethods[] = "    }\n";

        $builderMethods[] = "    public static function fromArray(array \$src): {$this->name}Builder\n    {\n        \$builder = new {$this->name}Builder();";
        $builderMethods = array_merge($builderMethods, $fromArrayContent);
        $builderMethods[] = "        return \$builder;";
        $builderMethods[] = "    }\n";

        $builderMethods[] = "    public function create(): {$this->name}\n    {";
        $builderMethods[] = "        return new {$this->name}(";
        $builderMethods[] = join(",\n", $createContent);
        $builderMethods[] = "        );";
        $builderMethods[] = "    }";

        return join("\n", $builderMethods);
    }

    public function getSetters()
    {
        $setters = [];
        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            $setters[] = $t->setter($field, $this->name);
        }

        return join("\n\n", $setters);
    }

}