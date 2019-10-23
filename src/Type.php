<?php

namespace mgen;

class Type
{
    private $type;
    private $nullable;
    private static $testValues = [];

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

            case 'jsonarray':
                $detectedType = 'array';
                break;

            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return $detectedType . "|null";
        }
        return $detectedType;
    }

    public function getReturnType()
    {
        switch ($this->type) {
            case 'timestamp':
                $detectedType = 'int';
                break;

            case 'jsonarray':
                $detectedType = 'array';
                break;

            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return "?" . $detectedType;
        }
        return $detectedType;
    }

    public function getSourceType()
    {
        switch ($this->type) {
            case 'timestamp':
            case 'jsonarray':
                return "";
            default:
                $detectedType = $this->type;
        }
        if ($this->nullable) {
            return "?" . $detectedType;
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
            $s .= "\n\n    public function get" . str_replace("Ts", "Time", ucfirst($field)) . "()";
            $s .= ": string";
            $s .= "\n    {\n";
            $s .= "        return \\mgen\\ext\\Utils::date(\$this->{$field});\n";
            $s .= "    }";
        }

        if ($this->type == 'jsonarray') {
            $s .= "\n\n" . S . "public function get" . ucfirst($field) . "Json(): string";
            $s .= "\n    {\n";
            $s .= "        return json_encode(\$this->{$field});\n";
            $s .= "    }";
        }

        return $s;
    }

    public function setter($field, $model)
    {
        $s = "    public function set" . ucfirst($field) . "(";
        $s .= ($this->getSourceType() ? $this->getSourceType() . " " : "") . "\${$field}): {$model}Builder\n";
        $s .= "    {\n";
        if ($this->type === 'timestamp') {
            $s .= S . S . "\$this->{$field} = ";
            $s .= "\\mgen\\ext\\Utils::tsValue(\${$field});";
        } elseif ($this->type === 'jsonarray') {
            if ($this->nullable) {
                $s .= S . S . "if (is_null(\${$field})) {\n";
                $s .= S . S . S . "\$this->{$field} = null;\n";
                $s .= S . S . "else";
            }
            $s .= S . S . "if (!\${$field}) {\n";
            $s .= S . S . S . "\$this->{$field} = [];\n";
            $s .= S . S . "} elseif (is_array(\${$field})) {\n";
            $s .= S . S . S . "\$this->{$field} = \${$field};\n";
            $s .= S . S . "} elseif (json_decode(\${$field}) !== false) {\n";
            $s .= S . S . S . "\$this->{$field} = json_decode(\${$field}, true);\n";
            $s .= S . S . "} else {\n";
            $s .= S . S . S . "\$this->{$field} = [\${$field}];\n";
            $s .= S . S . "}";
        } else {
            $s .= S . S . "\$this->{$field} = ";
            $s .= "\${$field};";
        }
        $s .= "\n        return \$this;\n    }";
        return $s;
    }

    public function getCtorAssign($field)
    {
        if ($this->type === 'timestamp') {
            return "        \$this->{$field} = \\mgen\\ext\\Utils::tsValue(\${$field});";
        } elseif ($this->type === 'jsonarray') {
            $s = "";
            if ($this->nullable) {
                $s .= S . S . "if (is_null(\${$field})) {\n";
                $s .= S . S . S . "\$this->{$field} = null;\n";
                $s .= S . S . "else";
            }
            $s .= S . S . "if (!\${$field}) {\n";
            $s .= S . S . S . "\$this->{$field} = [];\n";
            $s .= S . S . "} elseif (is_array(\${$field})) {\n";
            $s .= S . S . S . "\$this->{$field} = \${$field};\n";
            $s .= S . S . "} elseif (json_decode(\${$field}) !== false) {\n";
            $s .= S . S . S . "\$this->{$field} = json_decode(\${$field}, true);\n";
            $s .= S . S . "} else {\n";
            $s .= S . S . S . "\$this->{$field} = [\${$field}];\n";
            $s .= S . S . "}";
            $s .= "";
            return $s;
        }
        return "        \$this->{$field} = \${$field};";
    }

    public function getSQLName($field)
    {
        if ($this->nullable) {
            return 'null';
        }
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

            case 'jsonarray':
                $dt = 'VARCHAR(1024)';
                break;

            default:
                throw new \Exception("Invalid datatype " . $this->type);
        }
        return Helper::camelCaseToUnderscores($field) . " " . $dt . ($this->nullable ? "" : " NOT NULL");
    }

    public static function resetTestValues()
    {
        self::$testValues = [];
    }

    public function getDefaultValue()
    {
        if ($this->nullable) {
            return 'null';
        }
        switch ($this->type) {
            case 'int':
                return 0;

            case 'timestamp':
                return 0;

            case 'string':
                return '""';

            case 'float':
                return 0;

            case 'bool':
                return 'false';

            case 'jsonarray':
                return '"[]"';

            default:
                throw new \Exception("Invalid datatype " . $this->type);
        }
    }

    public function getQuotedValue($value)
    {
        if ($this->nullable) {
            return 'null';
        }
        switch ($this->type) {
            case 'int':
                return intval($value);

            case 'timestamp':
                return intval($value);

            case 'string':
                return '"' . $value . '"';

            case 'float':
                return floatval($value);

            case 'bool':
                return !!$value;

            case 'jsonarray':
                return '"' . $value . '"';

            default:
                throw new \Exception("Invalid datatype " . $this->type);
        }
    }

    public static function getTestValue($type, $idx)
    {
        if (isset(self::$testValues[$idx])) {
            return self::$testValues[$idx];
        }

        $t = new Type($type);
        switch ($t->type) {
            case 'int':
                return self::$testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return self::$testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return self::$testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return self::$testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return self::$testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            case 'jsonarray':
                return self::$testValues[$idx] = '["' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"]';

            default:
                throw new \Exception("Invalid datatype " . $type);
        }
    }

    public static function getTestValueNull($type, $idx)
    {
        if (isset(self::$testValues[$idx])) {
            return self::$testValues[$idx];
        }

        $t = new Type($type);
        if ($t->nullable) {
            return self::$testValues[$idx] = 'null';
        }

        switch ($t->type) {
            case 'int':
                return self::$testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return self::$testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return self::$testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return self::$testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return self::$testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            case 'jsonarray':
                return self::$testValues[$idx] = '["' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"]';

            default:
                throw new \Exception("Invalid datatype " . $type);
        }
    }

    public static function getTestValueTs($type, $idx, $intForTs)
    {
        $t = new Type($type);
        if (isset(self::$testValues[$idx])) {
            if ($t->type != 'timestamp') {
                return self::$testValues[$idx];
            } else {
                return $intForTs ? self::$testValues[$idx] : ('"' . date('Y-m-d H:i:s', self::$testValues[$idx]) . '"');
            }
        }

        switch ($t->type) {
            case 'int':
                return self::$testValues[$idx] = rand(1, 100);

            case 'timestamp':
                self::$testValues[$idx] = 30000000 + rand(1, 1000) * 10;
                return $intForTs ? self::$testValues[$idx] : ('"' . date('Y-m-d H:i:s', self::$testValues[$idx]) . '"');

            case 'string':
                return self::$testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return self::$testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return self::$testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            case 'jsonarray':
                return self::$testValues[$idx] = '["' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"]';

            default:
                throw new \Exception("Invalid datatype " . $type);
        }
    }

    public static function getTestValueJson($type, $idx, $valueType)
    {
        $t = new Type($type);
        if (isset(self::$testValues[$idx])) {
            if ($type != 'jsonarray') {
                return self::$testValues[$idx];
            }
            $value = self::$testValues[$idx];
            switch ($valueType) {
                case 'json': return "/*1*/\"[\\\"$value\\\"]\""; // Json string
                case 'string': return "/*2*/\"$value\""; // Simple string
//                case 'empty': return self::$testValues[$idx]; // Empty value
                case 'array': return "/*3*/[\"$value\"]"; // Array
                case 'empty': return "\"\"";
                case 'emptyarray': return "[]";
                case 'emptyjson': return "\"[]\"";
            }
            throw new \Exception("Invalid value type " . $valueType);
        }


        switch ($t->type) {
            case 'int':
                return self::$testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return self::$testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return self::$testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return self::$testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return self::$testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            case 'jsonarray':
                $value = substr(
                    str_shuffle(
                        str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                    ),
                    1,
                    16
                );

                self::$testValues[$idx] = $value;

                switch ($valueType) {
                    case 'json': return "\"[\\\"$value\\\"]\""; // Json string
                    case 'string': return "\"$value\""; // Simple string
                    case 'empty': self::$testValues[$idx] = ""; return "\"\""; // Empty value
                    case 'array': return "[\"$value\"]"; // Array
                }
                throw new \Exception("Invalid value type " . $valueType);

            default:
                throw new \Exception("Invalid datatype " . $type);
        }
    }

    public static function getTestValueIncremented($type, $idx)
    {
        $t = new Type($type);
        if (isset(self::$testValues[$idx])) {
            switch ($t->type) {
                case 'int':
                    return self::$testValues[$idx] + 1;

                case 'timestamp':
                    return self::$testValues[$idx] + 1;

                case 'string':
                    return self::$testValues[$idx] . ' . "123"';

                case 'float':
                    return self::$testValues[$idx] + 1.3;

                case 'bool':
                    return self::$testValues[$idx] === 'true' ? 'false' : 'true';

                case 'jsonarray':
                    return preg_replace('!"]$!', 'abc"]', self::$testValues[$idx]);

                default:
                    throw new \Exception("Invalid datatype " . $type);
            }
        }

        switch ($t->type) {
            case 'int':
                return self::$testValues[$idx] = rand(1, 100);

            case 'timestamp':
                return self::$testValues[$idx] = 30000000 + rand(1, 1000) * 10;

            case 'string':
                return self::$testValues[$idx] = '"' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"';

            case 'float':
                return self::$testValues[$idx] = rand(1, 100) + 0.5;

            case 'bool':
                return self::$testValues[$idx] = (rand(1, 100) > 50) ? 'true' : 'false';

            case 'jsonarray':
                return self::$testValues[$idx] = '["' . substr(
                        str_shuffle(
                            str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', 10 * ceil(16 / strlen($x)))
                        ),
                        1,
                        16
                    ) . '"]';

            default:
                throw new \Exception("Invalid datatype " . $type);
        }
    }
}