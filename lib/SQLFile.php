<?php


class SQLFile extends CommonFile
{
    public function generate()
    {

        $file = [
            "-- This file was generated by modelGen",
            "-- VERSION: {$this->modelHash}",
            "-- https://github.com/alexshadie/modelGen",
            "CREATE TABLE " . camelCaseToUnderscores($this->name) . " (",
        ];
        $file[] = $this->buildFields();
        $file[] = ");";
        $file[] = "";
        return join("\n", $file);
    }

    private function buildFields()
    {
        $sqlFields = [];
        foreach ($this->fields as $field => $type) {
            $t = new Type($type);
            $sqlFields[] = S . $t->getSQLName($field);
        }
        return join(",\n", $sqlFields);
    }

}