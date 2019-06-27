<?php

namespace mgen;

class TestFile extends CommonFile
{
    public function generate()
    {
        $content = ["<?php", "/*", " * This file was generated by modelGen", " * VERSION: {$this->modelHash}", " * https://github.com/alexshadie/modelGen", " */", ""];
        if ($this->namespace) {
            $content[] = "namespace {$this->namespace};";
            $content[] = "";
        }
        $content[] = "use PHPUnit\Framework\TestCase;";
        $content[] = "";
        $content[] = "class {$this->name}Test extends TestCase\n{";
        $content[] = "";
        $content[] = $this->buildTest();
        $content[] = $this->buildTestEquals();
        $content[] = "}";
        $content[] = "";

        return join("\n", $content);
    }

    protected function buildTest()
    {
        $null = false;
        $ts = false;

        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            if ($type === 'timestamp') {
                $ts = true;
            }
            if ($t->isNullable()) {
                $null = true;
            }
        }

        $test1Init = [];
        $test1Assert = [];
        $test2Init = [];
        $test2Assert = [];
        $test3Init = [];
        $test3Assert = [];
        $test4Init = [];
        $test4Assert = [];
        $test5Init = [];
        $test5Assert = [];
        $test6Init = [];
        $test6Assert = [];
        $test7Init = [];
        $test7Assert = [];

        // Simple
        $test[] = S . "public function testCreate()";
        $test[] = S . "{";
        $test[] = S . S . "\$m = new {$this->name}(";
        $idx = 0;
        Type::resetTestValues();
        foreach ($this->fields as $field => $type) {
            $test1Init[] = S . S . S . Type::getTestValue($type, $field . "1");
            $test1Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "1") . ", \$m->get" . ucfirst($field) . "());";
            $test2Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValue($type, $field . "2") . ")";
            $test2Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "2") . ", \$m->get" . ucfirst($field) . "());";
            $test3Init[] = S . S . "\$src['$field'] = " . Type::getTestValue($type, $field . "3") . ";";
            $test3Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "3") . ", \$m->get" . ucfirst($field) . "());";
            $test4Init[] = S . S . "\$src[$idx] = " . Type::getTestValue($type, $field . "4") . ";";
            $test4Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "4") . ", \$m->get" . ucfirst($field) . "());";
            $test5Init[] = S . S . "\$src['" . Helper::camelCaseToUnderscores($field) . "'] = " . Type::getTestValue($type, $field . "5") . ";";
            $test5Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "5") . ", \$m->get" . ucfirst($field) . "());";
            $test6Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValue($type, $field . "2") . ")";
            $test6Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "2") . ", \$m->get" . ucfirst($field) . "());";
            $test7Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValue($type, $field . "7") . ")";
            $test7Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValue($type, $field . "7") . ", \$m->get" . ucfirst($field) . "());";
            $idx++;
        }


        $test[] = join(",\n", $test1Init);
        $test[] = S . S . ");\n";

        $test[] = join("\n", $test1Assert) . "\n";

        $test[] = S . S . "\$m = (new {$this->name}Builder())";
        $test[] = join("\n", $test2Init);
        $test[] = S . S . S . "->create();\n";

        $test[] = join("\n", $test2Assert) . "\n";

        $test[] = S . S . "\$src = [];";
        $test[] = join("\n", $test3Init);
        $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
        $test[] = S . S . S . "->create();";

        $test[] = join("\n", $test3Assert) . "\n";

        $test[] = S . S . "\$src = [];";
        $test[] = join("\n", $test4Init);
        $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
        $test[] = S . S . S . "->create();";

        $test[] = join("\n", $test4Assert) . "\n";

        $test[] = S . S . "\$src = [];";
        $test[] = join("\n", $test5Init);
        $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
        $test[] = S . S . S . "->create();";

        $test[] = join("\n", $test5Assert) . "\n";

        $test[] = S . S . "\$m = {$this->name}::build()";
        $test[] = join("\n", $test6Init);
        $test[] = S . S . S . "->create();";

        $test[] = join("\n", $test6Assert) . "\n";

        $test[] = S . S . "\$m1 = (new {$this->name}Builder())";
        $test[] = join("\n", $test7Init);
        $test[] = S . S . S . "->create();\n";
        $test[] = S . S . "\$m = {$this->name}Builder::from(\$m1)";
        $test[] = S . S . S . "->create();";

        $test[] = join("\n", $test7Assert) . "\n";

        $test[] = S . "}";

        // With NULLs

        if ($null) {
            $test1Init = [];
            $test1Assert = [];
            $test2Init = [];
            $test2Assert = [];
            $test3Init = [];
            $test3Assert = [];
            $test4Init = [];
            $test4Assert = [];
            $test5Init = [];
            $test5Assert = [];
            $test6Init = [];
            $test6Assert = [];
            $test7Init = [];
            $test7Assert = [];

            $test[] = "";
            $test[] = S . "public function testCreateNulls()";
            $test[] = S . "{";
            $test[] = S . S . "\$m = new {$this->name}(";
            $idx = 0;
            Type::resetTestValues();
            foreach ($this->fields as $field => $type) {
                $test1Init[] = S . S . S . Type::getTestValueNull($type, $field . "1");
                $test1Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "1") . ", \$m->get" . ucfirst($field) . "());";
                $test2Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueNull($type, $field . "2") . ")";
                $test2Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "2") . ", \$m->get" . ucfirst($field) . "());";
                $test3Init[] = S . S . "\$src['$field'] = " . Type::getTestValueNull($type, $field . "3") . ";";
                $test3Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "3") . ", \$m->get" . ucfirst($field) . "());";
                $test4Init[] = S . S . "\$src[$idx] = " . Type::getTestValueNull($type, $field . "4") . ";";
                $test4Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "4") . ", \$m->get" . ucfirst($field) . "());";
                $test5Init[] = S . S . "\$src['" . Helper::camelCaseToUnderscores($field) . "'] = " . Type::getTestValueNull($type, $field . "5") . ";";
                $test5Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "5") . ", \$m->get" . ucfirst($field) . "());";
                $test6Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueNull($type, $field . "6") . ")";
                $test6Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "6") . ", \$m->get" . ucfirst($field) . "());";
                $test7Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueNull($type, $field . "7") . ")";
                $test7Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueNull($type, $field . "7") . ", \$m->get" . ucfirst($field) . "());";

                $idx++;
            }

            $test[] = join(",\n", $test1Init);
            $test[] = S . S . ");\n";

            $test[] = join("\n", $test1Assert) . "\n";

            $test[] = S . S . "\$m = (new {$this->name}Builder())";
            $test[] = join("\n", $test2Init);
            $test[] = S . S . S . "->create();\n";

            $test[] = join("\n", $test2Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test3Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test3Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test4Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test4Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test5Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test5Assert) . "\n";

            $test[] = S . S . "\$m = {$this->name}::build()";
            $test[] = join("\n", $test6Init);
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test6Assert) . "\n";

            $test[] = S . S . "\$m1 = (new {$this->name}Builder())";
            $test[] = join("\n", $test7Init);
            $test[] = S . S . S . "->create();\n";
            $test[] = S . S . "\$m = {$this->name}Builder::from(\$m1)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test7Assert) . "\n";

            $test[] = S . "}";
        }

        // With TS

        if ($ts) {
            $test1Init = [];
            $test1Assert = [];
            $test2Init = [];
            $test2Assert = [];
            $test3Init = [];
            $test3Assert = [];
            $test4Init = [];
            $test4Assert = [];
            $test5Init = [];
            $test5Assert = [];
            $test6Init = [];
            $test6Assert = [];
            $test7Init = [];
            $test7Assert = [];
            $idx = 0;

            $test[] = "";
            $test[] = S . "public function testCreateTs()";
            $test[] = S . "{";
            $test[] = S . S . "\$m = new {$this->name}(";
            Type::resetTestValues();
            foreach ($this->fields as $field => $type) {
                $test1Init[] = S . S . S . Type::getTestValueTs($type, $field . "1", 0);
                $test1Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "1", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test1Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "1", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }
                $test2Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueTs($type, $field . "2", 0) . ")";
                $test2Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "2", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test2Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "2", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }

                $test3Init[] = S . S . "\$src['$field'] = " . Type::getTestValueTs($type, $field . "3", 0) . ";";
                $test3Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "3", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test3Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "3", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }
                $test4Init[] = S . S . "\$src[$idx] = " . Type::getTestValueTs($type, $field . "4", 0) . ";";
                $test4Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "4", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test4Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "4", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }
                $test5Init[] = S . S . "\$src['" . Helper::camelCaseToUnderscores($field) . "'] = " . Type::getTestValueTs($type, $field . "5", 0) . ";";
                $test5Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "5", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test5Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "5", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }
                $test6Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueTs($type, $field . "6", 0) . ")";
                $test6Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "6", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test6Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "6", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }


                $test7Init[] = S . S . S . "->set" . ucfirst($field) . "(" . Type::getTestValueTs($type, $field . "7", 0) . ")";
                $test7Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "7", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp' || $type == '?timestamp') {
                    $test7Assert[] = S . S . "\$this->assertEquals(" . Type::getTestValueTs($type, $field . "7", 0) . ", \$m->get" . str_replace("Ts", "Time", ucfirst($field)) . "());";
                }
                $idx++;
            }

            $test[] = join(",\n", $test1Init);
            $test[] = S . S . ");\n";

            $test[] = join("\n", $test1Assert) . "\n";

            $test[] = S . S . "\$m = (new {$this->name}Builder())";
            $test[] = join("\n", $test2Init);
            $test[] = S . S . S . "->create();\n";

            $test[] = join("\n", $test2Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test3Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test3Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test4Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test4Assert) . "\n";

            $test[] = S . S . "\$src = [];";
            $test[] = join("\n", $test5Init);
            $test[] = S . S . "\$m = {$this->name}Builder::fromArray(\$src)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test5Assert) . "\n";

            $test[] = S . S . "\$m = {$this->name}::build()";
            $test[] = join("\n", $test6Init);
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test6Assert) . "\n";

            $test[] = S . S . "\$m1 = (new {$this->name}Builder())";
            $test[] = join("\n", $test7Init);
            $test[] = S . S . S . "->create();\n";
            $test[] = S . S . "\$m = {$this->name}Builder::from(\$m1)";
            $test[] = S . S . S . "->create();";

            $test[] = join("\n", $test7Assert) . "\n";

            $test[] = S . "}";
        }

        $ctorArgs = [];
        $phpDocs = [];
        $assign = [];
        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            $phpDoc = S . "/** \n     * @var {$t->getPhpDocType()}\n";
            if (in_array($field, $this->exports)) {
                $phpDoc .= S . " * @@export\n";
            }
            $phpDoc .= S . " */";
            $phpDocs[] = $phpDoc;

            $ctorArgs[] = "{$t->getSourceType()} \${$field}";
            $assign[] = $t->getCtorAssign($field);
        }

        return join("\n", $test);
    }

    protected function buildTestEquals()
    {
        $test1Init = [];

        // Simple
        $test = [""];
        $test[] = S . "public function testEquals()";
        $test[] = S . "{";
        $test[] = S . S . "\$m = new {$this->name}(";

        $nonEquals = [];
        $eqInit = [];

        $asserts = [];

        Type::resetTestValues();
        foreach ($this->fields as $field => $type) {
            $test1Init[] = S . S . S . Type::getTestValue($type, $field . "1");
            $eqInit[] = S . S . S . Type::getTestValue($type, $field . "1");
            $noneqInit = [];
            foreach ($this->fields as $field1 => $type1) {
                $value = ($field1 === $field) ? Type::getTestValueIncremented($type, $field1 . "1") : Type::getTestValue($type1, $field1 . "1");
                $noneqInit[] = S . S . S . $value;

            }

            $asserts[] = S . S . "\$this->assertFalse(\$m->equals(\$mne" . ucfirst($field) . "));";
            $asserts[] = S . S . "\$this->assertFalse(\$mne" . ucfirst($field) . "->equals(\$m));";

            $nonEquals[] = S . S . "\$mne" . ucfirst($field) . " = new {$this->name}(";
            $nonEquals[] = join(",\n", $noneqInit);
            $nonEquals[] = S . S . ");\n";

        }

        $test[] = join(",\n", $test1Init);
        $test[] = S . S . ");\n";

        $test[] = S . S . "\$me = new {$this->name}(";
        $test[] = join(",\n", $eqInit);
        $test[] = S . S . ");\n";

        $test[] = join("\n", $nonEquals);

        $test[] = S . S . "\$this->assertTrue(\$m->equals(\$me));";
        $test[] = S . S . "\$this->assertTrue(\$me->equals(\$m));";
        $test[] = S . S . "\$this->assertFalse(\$m->equals(null));";
        $test[] = join("\n", $asserts);

        $test[] = S . "}";

        return join("\n", $test);
    }
}
