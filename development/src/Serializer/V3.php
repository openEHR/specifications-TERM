<?php

namespace OpenEHR\Specifications\Tools\Terminology\Serializer;

use DOMDocument;
use OpenEHR\Specifications\Tools\Terminology\Model\Terminology;
use SimpleXMLElement;
use const OpenEHR\Specifications\Tools\Terminology\VERSION;

class V3 {

    /** @noinspection NullPointerExceptionInspection */
    public static function asXml(Terminology $terminology): string {
        $xml = new SimpleXMLElement('<terminology/>');
        $xml['name'] = $terminology->name;
        $xml['language'] = $terminology->language;
        $xml['version'] = $terminology->version ?: VERSION;
        $xml['date'] = $terminology->date ?: date('Y-m-d');
        foreach ($terminology->codeSets as $codeSet) {
            $codeSetNode = $xml->addChild('codeset');
            $codeSetNode->addAttribute('issuer', $codeSet->issuer);
            $codeSetNode->addAttribute('openehr_id', $codeSet->openehr_id);
            $codeSetNode->addAttribute('name', $codeSet->name);
            if (!empty($codeSet->external_id)) {
                $codeSetNode->addAttribute('external_id', $codeSet->external_id);
            }
            if (!empty($codeSet->status)) {
                $codeSetNode->addAttribute('status', $codeSet->status);
            }
            foreach ($codeSet->translations as $lang => $name) {
                $translation = $codeSetNode->addChild('name', $name);
                $translation->addAttribute('lang', $lang);
            }
            foreach ($codeSet->codings as $coding) {
                $codeNode = $codeSetNode->addChild('code');
                $codeNode->addAttribute('value', $coding->code);
                if (!empty($coding->description)) {
                    $codeNode->addAttribute('description', $coding->description);
                }
                if (!empty($coding->status)) {
                    $codeNode->addAttribute('status', $coding->status);
                }
                foreach ($coding->translations as $lang => $description) {
                    $translation = $codeNode->addChild('description', $description);
                    $translation->addAttribute('lang', $lang);
                }
            }
        }
        /** @var DOMDocument $dom */
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

}
