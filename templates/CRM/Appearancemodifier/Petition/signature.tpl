{crmAPI var="modifiedPetition" entity="AppearancemodifierPetition" action="get" version="3" survey_id=$survey_id}

{include file="CRM/Appearancemodifier/commonmodifiedsettings.tpl" modifiedSetting=$modifiedPetition}

{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].signers_block_position)}
    {crmAPI var='numberOfSigners' entity='Activity' action='getcount' sequential=0 activity_type_id="Petition" source_record_id=$survey_id}
{/if}
{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].signers_block_position) && $modifiedPetition.values[0].signers_block_position eq 'top_number'}
    {include file="CRM/Appearancemodifier/Petition/signersnumber.tpl"}
{/if}
{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].signers_block_position) && $modifiedPetition.values[0].signers_block_position eq 'top_progress' && isset($modifiedPetition.values[0].target_number_of_signers)}
    {assign var=targetNumberOfSigners value=$modifiedPetition.values[0].target_number_of_signers}
    {include file="CRM/Appearancemodifier/Petition/signersprogressbar.tpl"}
{/if}

{include file="CRM/Campaign/Form/Petition/Signature.tpl"}

{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].additional_note)}
    <div class="appearancemodifier-additional-note">{$modifiedPetition.values[0].additional_note}</div>
{/if}

{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].signers_block_position) && $modifiedPetition.values[0].signers_block_position eq 'bottom_number'}
    {include file="CRM/Appearancemodifier/Petition/signersnumber.tpl"}
{/if}
{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].signers_block_position) && $modifiedPetition.values[0].signers_block_position eq 'bottom_progress' && isset($modifiedPetition.values[0].target_number_of_signers)}
    {assign var=targetNumberOfSigners value=$modifiedPetition.values[0].target_number_of_signers}
    {include file="CRM/Appearancemodifier/Petition/signersprogressbar.tpl"}
{/if}
