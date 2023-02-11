<?php

namespace OpenEHR\Specifications\Tools\Terminology\Model;

class Terminology {

    public string $name;

    public string $language;

    public string $filename;

    public string $version;

    public string $date;

    /** @var CodeSet[] */
    public array $codeSets = [];

}
