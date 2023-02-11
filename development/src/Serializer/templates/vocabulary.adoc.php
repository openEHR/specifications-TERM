<?php
/** @var OpenEHR\Specifications\Tools\Terminology\Model\CodeSet $codeSet */

use OpenEHR\Specifications\Tools\Terminology\Serializer\Fhir;

?>
=== <?=ucwords($codeSet->name)?>

[cols="6,8,1"]
|===
3+h| *Vocabulary details*

|Name: *<?=$codeSet->name?>* +
    Id: __<?=$codeSet->openehr_id?>__ +
    Status: __<?=$codeSet->status?:'active'?>__ +
    Languages:  __<?=implode(', ', array_keys($codeSet->translations))?>__ +
    FHIR Terminology: `<?= Fhir::encodeCodeSystemUrl($codeSet)?>[CodeSystem^], <?= Fhir::encodeValueSetUrl($codeSet)?>[ValueSet^]`

2+| {<?=$codeSet->openehr_id?>_description} +
    +
    Used in: `{<?=$codeSet->openehr_id?>_links}`

h| *Code*      h| *Description*       h| *Status*
<?php foreach ($codeSet->codings as $coding): ?>
| *<?=$coding->code?>*      a| <?=$coding->description?:' '?>       | <?=$coding->status?:'active'?>

<?php endforeach; ?>
|===

