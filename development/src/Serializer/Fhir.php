<?php

namespace OpenEHR\Specifications\Tools\Terminology\Serializer;

use DOMDocument;
use OpenEHR\Specifications\Tools\Terminology\Model\CodeSet;
use OpenEHR\Specifications\Tools\Terminology\Model\Terminology;
use SimpleXMLElement;
use const OpenEHR\Specifications\Tools\Terminology\VERSION;

class Fhir {

    public const CodeSystemPath = 'FHIR/codesystem-';
    public const ValueSetPath = 'FHIR/valueset-';

    /** @noinspection NullPointerExceptionInspection */
    public static function asCodeSystemXml(CodeSet $codeSet, Terminology $terminology): string {
        $xml = new SimpleXMLElement('<CodeSystem xmlns="http://hl7.org/fhir"/>');
        $xml->addchild('url')->addAttribute('value', self::encodeCodeSystemUrl($codeSet));
        self::addCommonNodes($xml, $codeSet, $terminology);
        $xml->addchild('content')->addAttribute('value', 'complete');
        $xml->addchild('count')->addAttribute('value', count($codeSet->codings));
        foreach ($codeSet->codings as $coding) {
            $codeNode = $xml->addChild('concept');
            $codeNode->addchild('code')->addAttribute('value', $coding->code);
            $codeNode->addchild('display')->addAttribute('value', $coding->description ?: $coding->code);
            foreach ($coding->translations as $lang => $description) {
                if ($lang === 'en') {
                    continue;
                }
                $designation = $codeNode->addChild('designation');
                $designation->addchild('language')->addAttribute('value', $lang);
                $designation->addchild('value')->addAttribute('value', $description);
            }
        }
        return self::getXmlString($xml);
    }

    /** @noinspection NullPointerExceptionInspection */
    public static function asValueSetXml(CodeSet $codeSet, Terminology $terminology): string {
        $xml = new SimpleXMLElement('<ValueSet xmlns="http://hl7.org/fhir"/>');
        $xml->addchild('url')->addAttribute('value', self::encodeValueSetUrl($codeSet));
        self::addCommonNodes($xml, $codeSet, $terminology);
        $compose = $xml->addchild('compose');
        $compose->addchild('lockedDate')->addAttribute('value', $terminology->date ?: date('Y-m-d'));
        $include = $compose->addchild('include');
        $include->addchild('system')->addAttribute('value', self::encodeCodeSystemUrl($codeSet));
        $include->addchild('version')->addAttribute('value', $terminology->version ?: VERSION);
        return self::getXmlString($xml);
    }

    /** @noinspection NullPointerExceptionInspection */
    private static function addCommonNodes(SimpleXMLElement $xml, CodeSet $codeSet, Terminology $terminology): void {
        $xml->addchild('version')->addAttribute('value', $terminology->version ?: VERSION);
        $xml->addchild('name')->addAttribute('value', self::encodeName($codeSet));
        $xml->addchild('title')->addAttribute('value', self::encodeTitle($codeSet));
        $xml->addchild('status')->addAttribute('value', self::encodeStatus($codeSet));
        $xml->addchild('experimental')->addAttribute('value', 'false');
        $xml->addchild('date')->addAttribute('value', $terminology->date ?: date('Y-m-d'));
        $xml->addchild('publisher')->addAttribute('value', 'openEHR International');
        $contact = $xml->addchild('contact');
        $contact->addchild('name')->addAttribute('value', 'openEHR TERM specifications');
        $telecom = $contact->addchild('telecom');
        $telecom->addchild('system')->addAttribute('value', 'url');
        $telecom->addchild('value')->addAttribute('value', 'https://specifications.openehr.org/releases/TERM/Release-'.$terminology->version ?: VERSION);
        $xml->addchild('caseSensitive')->addAttribute('value', 'true');
    }

    /** @noinspection HttpUrlsUsage */
    public static function encodeCodeSystemUrl(CodeSet $codeSet): string {
        return 'https://specifications.openehr.org/' . self::CodeSystemPath . $codeSet->openehr_id;
    }

    /** @noinspection HttpUrlsUsage */
    public static function encodeValueSetUrl(CodeSet $codeSet): string {
        return 'https://specifications.openehr.org/' . self::ValueSetPath . $codeSet->openehr_id;
    }

    private static function getXmlString(SimpleXMLElement $xml): string {
        /** @var DOMDocument $dom */
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    public static function encodeName(CodeSet $codeSet): string {
        return $codeSet->openehr_id;
    }

    public static function encodeTitle(CodeSet $codeSet): string {
        return ucwords($codeSet->name);
    }

    public static function encodeStatus(CodeSet $codeSet): string {
        return match ($codeSet->status) {
            'draft' => 'draft',
            '', 'active' => 'active',
            'retired' => 'retired',
            default => 'unknown',
        };
    }

}
