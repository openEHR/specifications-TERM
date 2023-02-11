<?php

namespace OpenEHR\Specifications\Tools\Terminology;

use OpenEHR\Specifications\Tools\Terminology\Model\CodeSet;
use OpenEHR\Specifications\Tools\Terminology\Model\Coding;
use OpenEHR\Specifications\Tools\Terminology\Model\Terminology;
use SimpleXMLElement;

class Parser {

    /**
     * @param string $filename
     * @return Terminology
     * @noinspection DuplicatedCode
     */
    public static function parse(string $filename): Terminology {
        echo "Parser: reading $filename". PHP_EOL;
        $content = file_get_contents($filename) ?: '';
        $xml = simplexml_load_string($content) ?: new SimpleXMLElement('<terminology/>');
        $terminology = new Terminology();
        $terminology->filename = $filename;
        $terminology->name = $xml['name'] ?: 'unknown';
        $terminology->language = $xml['language'] ?: 'unknown';
        $terminology->version = $xml['version'] ?: '';
        $terminology->date = $xml['date'] ?: '';
        echo "Parser: $terminology->language: parsing ";
        foreach ($xml as $name => $node) {
            echo '.';
            $codeSet = new CodeSet();
            $codeSet->issuer = $node['issuer'] ?: 'openehr';
            $codeSet->openehr_id = $node['openehr_id'] ?: uniqid('', true);
            $codeSet->name = $node['name'] ?: $node['openehr_id'];
            $codeSet->external_id = $node['external_id'] ?: '';
            $codeSet->status = $node['status'] ?: '';
            switch ($name) {
                case "codeset":
                    $codeSet->issuer = $node['issuer'] ?: 'openehr';
                    $codeSet->openehr_id = $node['openehr_id'] ?: uniqid('', true);
                    $codeSet->name = $node['name'] ?: $node['openehr_id'];
                    $codeSet->external_id = $node['external_id'] ?: '';
                    $codeSet->status = $node['status'] ?: '';
                    foreach ($node as $subNode) {
                        $coding = new Coding();
                        $coding->code = $subNode['value'];
                        if (isset($subNode['description'])) {
                            $coding->description = $subNode['description'];
                        }
                        if (isset($subNode['status'])) {
                            $coding->status = $subNode['status'];
                        }
                        if (!empty($codeSet->codings[$coding->code])) {
                            echo " duplicate code '$coding->code|$coding->description' in ($codeSet->name) ". PHP_EOL;
                        }
                        $codeSet->codings[$coding->code] = $coding;
                    }
                    break;
                case "group":
                    foreach ($node as $subNode) {
                        $coding = new Coding();
                        $coding->code = $subNode['id'];
                        $coding->description = $subNode['rubric'];
                        if (isset($subNode['status'])) {
                            $coding->status = $subNode['status'];
                        }
                        if (!empty($codeSet->codings[$coding->code])) {
                            echo " duplicate code '$coding->code|$coding->description' in ($codeSet->name) ". PHP_EOL;
                        }
                        $codeSet->codings[$coding->code] = $coding;
                    }
                    break;
                default:
                    echo "==== Error $name ====";
                    continue 2;
            }
            $codeSet->openehr_id = preg_replace('/\W/', '_', $codeSet->openehr_id);
            if (!empty($terminology->codeSets[$codeSet->openehr_id])) {
                echo " duplicate codeSet $codeSet->openehr_id(name: $codeSet->name) ". PHP_EOL;
            }
            $terminology->codeSets[$codeSet->openehr_id] = $codeSet;
        }
        echo " done! ". PHP_EOL;
        return $terminology;
    }

}
