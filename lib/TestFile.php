<?php


class TestFile extends CommonFile
{
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

        // Simple
        $test[] = S . "public function testCreate()";
        $test[] = S . "{";
        $test[] = S . S . "\$m = new {$this->name}(";
        foreach ($this->fields as $field => $type) {
            $t = new Type($type);

            $test1Init[] = S.S.S.$t->getTestValue($field . "1");
            $test1Assert[] = S.S."\$this->assertEquals(" . $t->getTestValue($field . "1") . ", \$m->get" . ucfirst($field) . "());";
            $test2Init[] = S.S.S."->set" . ucfirst($field) . "(" . $t->getTestValue($field . "2") . ")";
            $test2Assert[] = S.S."\$this->assertEquals(" . $t->getTestValue($field . "2") . ", \$m->get" . ucfirst($field) . "());";
        }


        $test[] = join(",\n", $test1Init);
        $test[] = S . S . ");\n";

        $test[] = join("\n", $test1Assert) . "\n";

        $test[] = S . S . "\$m = (new {$this->name}Builder())";
        $test[] = join("\n", $test2Init);
        $test[] = S . S . S . "->create();\n";

        $test[] = join("\n", $test2Assert) . "\n";

        $test[] = S . "}";

        // With NULLs

        if ($null) {
            $test1Init = [];
            $test1Assert = [];
            $test2Init = [];
            $test2Assert = [];
            $test[] = S . "public function testCreateNulls()";
            $test[] = S . "{";
            $test[] = S . S . "\$m = new {$this->name}(";
            foreach ($this->fields as $field => $type) {
                $t = new Type($type);

                $test1Init[] = S.S.S.$t->getTestValueNull($field . "1");
                $test1Assert[] = S.S."\$this->assertEquals(" . $t->getTestValueNull($field . "1") . ", \$m->get" . ucfirst($field) . "());";
                $test2Init[] = S.S.S."->set" . ucfirst($field) . "(" . $t->getTestValueNull($field . "2") . ")";
                $test2Assert[] = S.S."\$this->assertEquals(" . $t->getTestValueNull($field . "2") . ", \$m->get" . ucfirst($field) . "());";
            }

            $test[] = join(",\n", $test1Init);
            $test[] = S . S . ");\n";

            $test[] = join("\n", $test1Assert) . "\n";

            $test[] = S . S . "\$m = (new {$this->name}Builder())";
            $test[] = join("\n", $test2Init);
            $test[] = S . S . S . "->create();\n";

            $test[] = join("\n", $test2Assert) . "\n";

            $test[] = S . "}";
        }

        // With TS

        if ($ts) {
            $test1Init = [];
            $test1Assert = [];
            $test2Init = [];
            $test2Assert = [];
            $test[] = S . "public function testCreateTs()";
            $test[] = S . "{";
            $test[] = S . S . "\$m = new {$this->name}(";
            foreach ($this->fields as $field => $type) {
                $t = new Type($type);

                $test1Init[] = S.S.S.$t->getTestValueTs($field . "1", 0);
                $test1Assert[] = S.S."\$this->assertEquals(" . $t->getTestValueTs($field . "1", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp') {
                    $test1Assert[] = S . S . "\$this->assertEquals(" . $t->getTestValueTs($field . "1", 0) . ", \$m->get" . ucfirst(str_replace("Ts", "Time", $field)) . "());";
                }
                $test2Init[] = S.S.S."->set" . ucfirst($field) . "(" . $t->getTestValueTs($field . "2", 0) . ")";
                $test2Assert[] = S.S."\$this->assertEquals(" . $t->getTestValueTs($field . "2", 1) . ", \$m->get" . ucfirst($field) . "());";
                if ($type == 'timestamp') {
                    $test2Assert[] = S . S . "\$this->assertEquals(" . $t->getTestValueTs($field . "2", 0) . ", \$m->get" . ucfirst(str_replace("Ts", "Time", $field)) . "());";
                }
            }

            $test[] = join(",\n", $test1Init);
            $test[] = S . S . ");\n";

            $test[] = join("\n", $test1Assert) . "\n";

            $test[] = S . S . "\$m = (new {$this->name}Builder())";
            $test[] = join("\n", $test2Init);
            $test[] = S . S . S . "->create();\n";

            $test[] = join("\n", $test2Assert) . "\n";

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

    public function generate()
    {
        $content = ["<?php", "/*", " * This file was generated by modelGen", " * https://github.com/alexshadie/modelGen", " */", ""];
        if ($this->namespace) {
            $content[] = "namespace {$this->namespace};";
            $content[] = "";
        }
        $content[] = "use PHPUnit\Framework\TestCase;";
        $content[] = "";
        $content[] = "class {$this->name}Test extends TestCase\n{";
        $content[] = "";
        $content[] = $this->buildTest();
        $content[] = "}";
        $content[] = "";

        return join("\n", $content);
    }
}
