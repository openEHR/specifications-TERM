<?php

namespace OpenEHR\Specifications\Tools\Terminology;


class Writer {

    public static function save(string $name, string $content): void {
        $basename = basename($name);
        echo "Writer: saving $basename - ";
        $name = str_replace('fhir', 'FHIR', $name);
        $bytes = file_put_contents($name, $content);
        echo "($bytes bytes) done!" . PHP_EOL;
    }
}
