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
}
