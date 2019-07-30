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

    public function serialize(): string
    {
        return json_encode([
            'class' => get_class($this),
            'data' => $this->toArray(),
        ]);
    }
}
