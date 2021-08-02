{crmAPI var="modifiedPetition" entity="AppearancemodifierPetition" action="get" version="3" uf_group_id=$survey_id}
{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedPetition.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}

{include file="CRM/Campaign/Form/Petition/Signature.tpl"}

{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].outro)}
    <div class="appearancemodifier-outro">{$modifiedPetition.values[0].outro}</div>
{/if}
