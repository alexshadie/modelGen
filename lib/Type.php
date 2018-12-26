<?php

class Type
{
    private $type;
    private $nullable;
    private $testValues = [];

    public function __construct(string $type)
    {
        $this->type = $type;
        if (strpos($type, '?') === 0) {
            $this->type = ltrim($type, '?');
            $this->nullable = true;
        }
    }

    public function isNullable()
    {
        return $this->nullable;
    }

    public function getPhpDocType()
    {
        switch ($this->type) {
            case 'timestamp':
                $detectedType = 'int';
                break;
            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return $detectedType . "|null";
        }
        return $detectedType;
    }

    public function getter($field)
    {
        $s = "    public function get" . ucfirst($field) . "()";
        if ($this->getReturnType()) {
            $s .= ": " . $this->getReturnType();
        }
        $s .= "\n    {\n";
        $s .= "        return \$this->{$field};\n";
        $s .= "    }";

        if ($this->type == 'timestamp' && strpos(ucfirst($field), 'Ts') !== 'false') {
            $s .= "\n    public function get" . str_replace("Ts", "Time", ucfirst($field)) . "()";
            $s .= ": string";
            $s .= "\n    {\n";
            $s .= "        return \core\Utils::date(\$this->{$field});\n";
            $s .= "    }";
        }
        return $s;
    }

    public function getReturnType()
    {
        switch ($this->type) {
            case 'timestamp':
                $detectedType = 'int';
                break;
            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return "?" . $detectedType;
        }
        return $detectedType;
    }

    public function setter($field, $model)
    {
        $s = "    public function set" . ucfirst($field) . "(";
        $s .= ($this->getSourceType() ? $this->getSourceType() . " " : "") . "\${$field}): {$model}Builder\n";
        $s .= "    {\n        \$this->{$field} = ";
        if ($this->type === 'timestamp') {
            $s .= "\core\Utils::tsValue(\${$field});";
        } else {
            $s .= "\${$field};";
        }
        $s .= "\n        return \$this;\n    }";
        return $s;
    }

    public function getSourceType()
    {
        switch ($this->type) {
            case 'timestamp':
                return "";
            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return "?" . $detectedType;
        }
        return $detectedType;
    }

    public function getCtorAssign($field)
    {
        if ($this->type == 'timestamp') {
            return "        \$this->{$field} = \core\Utils::tsValue(\${$field});";
        }
        return "        \$this->{$field} = \${$field};";
    }

    public function getSQLName($field)
    {
        switch ($this->type) {
            case 'int':
                $dt = "INT";
                break;

            case 'timestamp':
                $dt = "TIMESTAMP";
                break;

            case 'string':
                $dt = "VARCHAR(255)";
                break;

            case 'float':
                $dt = "DECIMAL(20,10)";
                break;

            case 'bool':
                $dt = 'TINYINT';
                break;

            default:
                throw new Exception("Invalid datatype " . $this->type);
        }
        return camelCaseToUnderscores($field) . " " . $dt . ($this->nullable ? "" : " NOT NULL");
    }

    public function resetTestValues()
    {
        $this->testValues = [];
    }

    public function getTestValue($idx)
    {
        if (isset($this->testValues[$idx])) {
            return $this->testValues[$idx];
        }

        switch ($this->type) {
            case 'int':
                return $this->testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return $this->testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return $this->testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return $this->testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return $this->testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            default:
                throw new Exception("Invalid datatype " . $this->type);
        }
    }

    public function getTestValueNull($idx)
    {
        if (isset($this->testValues[$idx])) {
            return $this->testValues[$idx];
        }

        if ($this->nullable) {
            return $this->testValues[$idx] = 'null';
        }

        switch ($this->type) {
            case 'int':
                return $this->testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return $this->testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return $this->testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return $this->testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return $this->testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            default:
                throw new Exception("Invalid datatype " . $this->type);
        }
    }

    public function getTestValueTs($idx, $intForTs)
    {
        if (isset($this->testValues[$idx])) {
            if ($this->type != 'timestamp') {
                return $this->testValues[$idx];
            } else {
                return $intForTs ? $this->testValues[$idx] : ('"' . date('Y-m-d H:i:s', $this->testValues[$idx]) . '"');
            }
        }

        switch ($this->type) {
            case 'int':
                return $this->testValues[$idx] = rand(1, 100);

            case 'timestamp':
                $this->testValues[$idx] = 30000000 + rand(1, 1000) * 10;
                return $intForTs ? $this->testValues[$idx] : ('"' . date('Y-m-d H:i:s', $this->testValues[$idx]) . '"');

            case 'string':
                return $this->testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return $this->testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return $this->testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            default:
                throw new Exception("Invalid datatype " . $this->type);
        }
    }
}