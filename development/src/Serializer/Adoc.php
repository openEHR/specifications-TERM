<?php

namespace OpenEHR\Specifications\Tools\Terminology\Serializer;

use OpenEHR\Specifications\Tools\Terminology\Model\CodeSet;
use OpenEHR\Specifications\Tools\Terminology\Model\Terminology;

class Adoc {

    public static function asAdoc(Terminology $terminology, string $filter): string {
        $content = '';
        foreach ($terminology->codeSets as $codeSet) {
            ob_start();
            if (!empty($codeSet->external_id) && in_array($filter, ['codesets', 'all'])) {
                self::render($codeSet, 'codeset');
            } elseif (empty($codeSet->external_id) && in_array($filter, ['vocabularies', 'all'])) {
                self::render($codeSet, 'vocabulary');

            }
            $content .= ob_get_clean();
        }
        return $content;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public static function render(CodeSet $codeSet, string $template): void {
        switch ($template) {
            case 'codeset':
                require 'templates/codeset.adoc.php';
                break;
            case 'vocabulary':
                require 'templates/vocabulary.adoc.php';
                break;
        }
    }

}
