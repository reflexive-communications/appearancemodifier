{crmAPI var="modifiedProfile" entity="AppearancemodifierProfile" action="get" version="3" uf_group_id=$groupId}

{include file="CRM/Appearancemodifier/commonmodifiedsettings.tpl" modifiedSetting=$modifiedProfile}

{include file="CRM/Profile/Form/Edit.tpl"}

{if $modifiedProfile.count eq '1' && isset($modifiedProfile.values[0].additional_note)}
    <div class="appearancemodifier-additional-note">{$modifiedProfile.values[0].additional_note}</div>
{/if}
