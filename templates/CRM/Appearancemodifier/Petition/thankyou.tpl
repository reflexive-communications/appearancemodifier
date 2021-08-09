{crmAPI var="modifiedPetition" entity="AppearancemodifierPetition" action="get" version="3" survey_id=$survey_id}
{if $modifiedPetition.count eq '1' && isset($modifiedPetition.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedPetition.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}

{include file="CRM/Campaign/Page/Petition/ThankYou.tpl"}
