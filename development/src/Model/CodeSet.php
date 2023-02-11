<?php

namespace OpenEHR\Specifications\Tools\Terminology\Model;

class CodeSet {

    public string $issuer;

    public string $openehr_id;

    public string $name;

    public string $external_id = '';

    public string $status = '';

    /** @var Coding[] */
    public array $codings = [];

    public array $translations = [];

}
