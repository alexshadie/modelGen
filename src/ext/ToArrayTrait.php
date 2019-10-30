<?php

namespace mgen\ext;

trait ToArrayTrait
{
    public function getJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $props = $reflection->getProperties();
        $result = [];
        foreach ($props as $prop) {
            if (strpos(str_replace("\n", " ", $prop->getDocComment()), '@@export')) {
                $prop->setAccessible(true);
                $result[$prop->getName()] = $prop->getValue($this);
            }
        }
        return $result;
    }

    protected static function camelToUnderscore($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public function toDbArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $props = $reflection->getProperties();
        $result = [];
        foreach ($props as $prop) {
            $prop->setAccessible(true);

            $result[self::camelToUnderscore($prop->getName())] = $prop->getValue($this);
        }
        return $result;
    }

    public function serialize(): string
    {
        return json_encode([
            'class' => get_class($this),
            'data' => $this->toArray(),
        ]);
    }
}
