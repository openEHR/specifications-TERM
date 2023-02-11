<?php
/** @var OpenEHR\Specifications\Tools\Terminology\Model\CodeSet $codeSet */
?>
=== <?=ucwords($codeSet->name)?>

[cols="6,8,1"]
|===
3+h| *Code Set details*

|Name: *<?=$codeSet->name?>* +
    Id: __<?=$codeSet->openehr_id?>__, External_id: __<?=$codeSet->external_id?>__ +
    Ref: {<?=$codeSet->openehr_id?>_ref} +
    Status: __<?=$codeSet->status?:'active'?>__
2+| {<?=$codeSet->openehr_id?>_description} +
    +
    Used in: `{<?=$codeSet->openehr_id?>_links}`

h| *Code*      h| *Description*       h| *Status*
<?php foreach ($codeSet->codings as $coding): ?>
| *<?=$coding->code?>*      a| <?=$coding->description?:' '?>       | <?=$coding->status?:'active'?>

<?php endforeach; ?>
|===

