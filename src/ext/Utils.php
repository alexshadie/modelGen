<?php

namespace mgen\ext;

class Utils
{
    public static function tsValue($ts): ?int
    {
        if (is_null($ts)) {
            return null;
        }
        if (is_numeric($ts)) {
            return $ts;
        }
        if (strtotime($ts)) {
            return strtotime($ts);
        }

        return null;
    }

    public static function date(?int $ts): ?string
    {
        if (is_null($ts)) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    public static function unserialize($serialized)
    {
        $serialized = json_decode($serialized, 1);
        $reflection = new \ReflectionClass($serialized['class']);

        $builder = $serialized['class'] . 'Builder';
        $builder = new $builder;

        $props = $reflection->getProperties();

        foreach ($props as $prop) {
            if (strpos(str_replace("\n", " ", $prop->getDocComment()), '@@export')) {
                $method = 'set' . ucfirst($prop->getName());
                $builder->$method($serialized['data'][$prop->getName()]);
            }
        }

        return $builder->create();
    }
}
