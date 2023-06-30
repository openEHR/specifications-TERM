<?php

namespace OpenEHR\Specifications\Tools\Terminology\Model;

class Aggregate extends Terminology {

    /** @var Terminology[] */
    public array $terminologies = [];

    public array $languages = [];

    /**
     * @param Terminology $terminology
     * @return $this
     */
    public function base(Terminology $terminology): self {
        echo "Aggregate: $terminology->language: load base ";
        foreach (get_object_vars($terminology) as $prop => $value) {
            echo '.';
            $this->$prop = $value;
        }
        $this->languages[$terminology->language] = $terminology->language;
        echo " done! " . PHP_EOL;
        return $this;
    }

    /**
     * @param Terminology $terminologyImport
     * @return $this
     */
    public function import(Terminology $terminologyImport): self {
        echo "Aggregate: $terminologyImport->language: importing ";
        $this->terminologies[$terminologyImport->language] = $terminologyImport;
        if (strcmp($terminologyImport->date, $this->date) > 0) {
            $this->date = $terminologyImport->date;
        }
        $neededCodeSetIds = array_flip(array_keys($this->codeSets));
        $newCodeSetIds = [];
        foreach ($terminologyImport->codeSets as $codeSetImport) {
            echo '.';
            if (!isset($this->codeSets[$codeSetImport->openehr_id])) {
                // if codeSet is not known yet, then we need to add it to $this->codeSets
                // but also add placeholders for all previous languages
                $this->codeSets[$codeSetImport->openehr_id] = $codeSetImport;
                $newCodeSetIds[] = $codeSetImport->openehr_id;
                foreach ($this->languages as $lang) {
                    $codeSetImport->translations[$lang] = $codeSetImport->openehr_id;
                    foreach ($codeSetImport->codings as $codingImport) {
                        $codingImport->translations[$lang] = $codingImport->code;
                    }
                }
            } else {
                // CodeSet is already known, we need to mark it as 'done'
                unset($neededCodeSetIds[$codeSetImport->openehr_id]);
            }
            // cursor codeSet, importing name and contained codes of that language
            $codeSet = $this->codeSets[$codeSetImport->openehr_id];
            $codeSet->translations[$terminologyImport->language] = $codeSetImport->name;
            // importing codings
            $neededCodings = array_flip(array_keys($codeSet->codings));
            $newCodings = [];
            foreach ($codeSetImport->codings as $codingImport) {
                if (!isset($codeSet->codings[$codingImport->code])) {
                    $newCodings[] = $codingImport->code;
                    continue;
                } else {
                    unset($neededCodings[$codingImport->code]);
                }
                $coding = $codeSet->codings[$codingImport->code];
                if (!empty($codingImport->description)) {
                    $coding->translations[$terminologyImport->language] = $codingImport->description;
                }
            }
            if (!empty($newCodings)) {
                echo "$terminologyImport->language: $codeSet->openehr_id: New Codings: " . implode(', ', $newCodings) . PHP_EOL;
            }
            if (!empty($neededCodings)) {
                echo "$terminologyImport->language: $codeSet->openehr_id: Codings missing: " . implode(', ', $neededCodings) . PHP_EOL;
            }
        }
        if (!empty($newCodeSetIds)) {
            echo "$terminologyImport->language: New CodeSets: " . implode(', ', $newCodeSetIds) . PHP_EOL;
        }
        if (!empty($neededCodeSetIds)) {
            echo "$terminologyImport->language: CodeSets missing: " . implode(', ', $neededCodeSetIds) . PHP_EOL;
        }
        $this->languages[$terminologyImport->language] = $terminologyImport->language;
        echo " done! " . PHP_EOL;
        return $this;
    }

    /**
     * @return $this
     */
    public function analyse(): self {
        echo "Aggregate: languages: " . implode(', ', $this->languages) . PHP_EOL;
        echo "Aggregate: codeSets: " . count($this->codeSets) . PHP_EOL;
        echo "Aggregate: checking duplicates ";
        $codes = [];
        $logs = [];
        foreach ($this->codeSets as $codeSet) {
            echo '.';
            foreach ($codeSet->codings as $coding) {
                if (!empty($codes[$coding->code])) {
                    $logs[] = "Aggregate: NOTICE: cross-duplicate '$coding->code' in ($codeSet->openehr_id, {$codes[$coding->code]})";
                }
                $codes[$coding->code] = $codeSet->openehr_id;
            }
        }
        echo " done! " . PHP_EOL;
        if ($logs) {
            echo implode(PHP_EOL, $logs) . PHP_EOL;
        }
        return $this;
    }

}
